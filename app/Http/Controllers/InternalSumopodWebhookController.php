<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InternalSumopodWebhookController extends Controller
{
    public function __invoke(Request $request, SubscriptionService $subscriptionService): JsonResponse
    {
        $rawBody = (string) $request->getContent();

        // 1. Verify internal webhook headers
        $webhookId = $request->header('X-Webhook-Id');
        $timestamp = (int) $request->header('X-Webhook-Timestamp');
        $environment = $request->header('X-Webhook-Environment');
        $signature = $request->header('X-Webhook-Signature');

        if (! $webhookId || ! $timestamp || ! $environment || ! $signature) {
            return response()->json(['error' => 'Missing internal webhook headers'], 400);
        }

        // Verify timestamp tolerance (300 seconds)
        if (abs(time() - $timestamp) > 300) {
            return response()->json(['error' => 'Webhook timestamp expired'], 401);
        }

        // Verify HMAC-SHA256 signature
        $secret = config('services.sumopod.internal_secret');
        if (empty($secret)) {
            Log::error('Fabriku internal sumopod webhook secret not configured');

            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $expectedSignature = 'v1,'.base64_encode(hash_hmac('sha256', "{$webhookId}.{$timestamp}.{$rawBody}", $secret, true));
        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('Fabriku internal sumopod webhook signature mismatch', ['id' => $webhookId]);

            return response()->json(['error' => 'Invalid internal webhook signature'], 401);
        }

        // Verify environment isolation
        $configuredEnv = config('services.sumopod.environment', 'live');
        if ($configuredEnv && $configuredEnv !== $environment) {
            Log::warning('Fabriku sumopod webhook environment mismatch', [
                'configured' => $configuredEnv,
                'received' => $environment,
            ]);

            return response()->json(['error' => 'Environment mismatch'], 403);
        }

        // 2. Parse payload
        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        $eventType = $payload['event_type'] ?? null;
        $data = $payload['data'] ?? [];

        if (! is_string($eventType) || ! in_array($eventType, ['payment.completed', 'payment.failed', 'payment.expired'], true)
            || ! is_string($data['order_id'] ?? null) || ! is_string($data['payment_id'] ?? null)
            || ! is_int($data['amount'] ?? null) || $data['amount'] <= 0) {
            return response()->json(['error' => 'Missing payload fields'], 422);
        }

        $orderId = $data['order_id'];
        $paymentId = $data['payment_id'];
        $amount = $data['amount'];

        // 3. Match prefix FAB-SUB-{reference}
        if (! str_starts_with($orderId, 'FAB-SUB-')) {
            return response()->json(['error' => 'Invalid order prefix for Fabriku'], 400);
        }

        // 4. Transaction & row lock
        return DB::transaction(function () use ($orderId, $paymentId, $amount, $eventType, $payload, $webhookId, $subscriptionService): JsonResponse {
            /** @var SubscriptionPayment|null $payment */
            $payment = SubscriptionPayment::query()
                ->where('payment_method', 'sumopod')
                ->where('provider', 'sumopod')
                ->where('provider_order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                Log::error('Fabriku sumopod webhook: subscription payment not found', ['order_id' => $orderId]);

                return response()->json(['error' => 'Subscription payment not found'], 404);
            }

            // Verify payment_id if payment already stored one
            if ($payment->provider_payment_id && $payment->provider_payment_id !== $paymentId) {
                Log::error('Fabriku sumopod webhook: payment_id mismatch', [
                    'stored' => $payment->provider_payment_id,
                    'received' => $paymentId,
                ]);

                return response()->json(['error' => 'Payment ID mismatch'], 422);
            }

            // 5. Compare amount strictly against SumoPod's own creation-time gross amount
            // (falls back to `amount` for payments created before this was captured). Never
            // compare against `amount` alone: SumoPod's "charge fee to customer" setting
            // makes the webhook's gross amount legitimately exceed the net `amount`.
            $expectedAmount = $payment->provider_amount ?? (int) $payment->amount;
            if ($amount !== $expectedAmount) {
                Log::error('Fabriku sumopod webhook: amount mismatch', [
                    'expected' => $expectedAmount,
                    'received' => $amount,
                ]);

                return response()->json(['error' => 'Amount mismatch'], 422);
            }

            // 6. Handle event type
            if ($eventType === 'payment.completed') {
                if ($payment->status === 'approved') {
                    return response()->json(['ok' => true, 'status' => 'already_approved']);
                }

                $payment->update([
                    'provider' => 'sumopod',
                    'provider_payment_id' => $paymentId,
                    'event_id' => $webhookId,
                    'provider_payload' => $payload,
                ]);

                $subscriptionService->activateSubscription($payment);

                return response()->json(['ok' => true, 'status' => 'approved']);
            }

            if ($eventType === 'payment.failed' || $eventType === 'payment.expired') {
                $subscriptionService->markFailed($payment, $eventType, $payload);

                return response()->json(['ok' => true, 'status' => $payment->fresh()->status]);
            }

            return response()->json(['ok' => true, 'status' => 'ignored']);
        });
    }
}
