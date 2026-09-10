<?php

namespace App\Services\Campaign;

use App\Mail\FeatureCampaignEmail;
use App\Models\CampaignLog;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CampaignSenderService
{
    public function __construct(
        protected CampaignLlmService $llmService
    ) {}

    /**
     * Send weekly campaign email to all active tenant admins
     *
     * @return array{batch_id: string, total: int, sent: int, failed: int, skipped: int, details: list<array<string, mixed>>}
     */
    public function sendWeeklyCampaign(?int $targetTenantId = null, bool $dryRun = false, bool $force = false): array
    {
        $isActive = SystemSetting::get('campaign_is_active', true);
        if (! $isActive && ! $force && ! $dryRun) {
            return [
                'batch_id' => '',
                'total' => 0,
                'sent' => 0,
                'failed' => 0,
                'skipped' => 0,
                'message' => 'Pengiriman kampanye email mingguan saat ini berstatus nonaktif di pengaturan.',
                'details' => [],
            ];
        }

        $batchId = 'batch_' . now()->format('Ymd_His') . '_' . Str::random(5);

        $query = Tenant::where('is_active', true);
        if ($targetTenantId) {
            $query->where('id', $targetTenantId);
        } else {
            $query->withoutDemo();
        }

        $tenants = $query->get();

        $sentCount = 0;
        $failedCount = 0;
        $skippedCount = 0;
        $details = [];

        foreach ($tenants as $tenant) {
            // Exclude demo tenant
            if ($tenant->isDemo()) {
                $skippedCount++;
                $details[] = [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'status' => 'skipped',
                    'reason' => 'Tenant demo dikecualikan dari campaign email.',
                ];
                continue;
            }

            // Find admin user for this tenant
            $adminUser = $tenant->users()
                ->where('role', 'admin')
                ->where('is_active', true)
                ->whereNotNull('email')
                ->first();

            if (! $adminUser || empty($adminUser->email)) {
                $skippedCount++;
                $details[] = [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'status' => 'skipped',
                    'reason' => 'Tenant tidak memiliki user admin aktif dengan email valid.',
                ];
                continue;
            }

            // Exclude demo admin user
            if ($adminUser->isDemo()) {
                $skippedCount++;
                $details[] = [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'recipient' => $adminUser->email,
                    'status' => 'skipped',
                    'reason' => 'User admin demo dikecualikan dari campaign email.',
                ];
                continue;
            }

            // Pick appropriate feature
            $feature = FabrikuFeatureCatalog::pickFeatureForTenant($tenant);

            // Generate content using LLM (with fallback)
            $campaignData = $this->llmService->generateCampaignContent($tenant, $adminUser, $feature);

            $subject = $campaignData['subject'] ?? "Tips & Fitur Fabriku: {$feature['name']}";
            $previewText = Str::limit($campaignData['intro'] ?? '', 150);

            try {
                // Render HTML content for logging and preview
                $renderedHtml = view('emails.feature-campaign', [
                    'tenant' => $tenant,
                    'adminUser' => $adminUser,
                    'campaignData' => $campaignData,
                ])->render();
            } catch (\Throwable $e) {
                $renderedHtml = '<p>' . htmlspecialchars($campaignData['intro'] ?? '') . '</p>';
            }

            if ($dryRun) {
                $details[] = [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'recipient' => $adminUser->email,
                    'feature' => $feature['name'],
                    'subject' => $subject,
                    'status' => 'dry_run',
                ];
                $sentCount++;
                continue;
            }

            try {
                // Send email
                Mail::to($adminUser->email)->send(new FeatureCampaignEmail($tenant, $adminUser, $campaignData));

                // Log successfully sent campaign
                CampaignLog::create([
                    'batch_id' => $batchId,
                    'tenant_id' => $tenant->id,
                    'user_id' => $adminUser->id,
                    'recipient_email' => $adminUser->email,
                    'recipient_name' => $adminUser->name,
                    'business_category' => $tenant->business_category ?? 'garment',
                    'feature_key' => $feature['key'] ?? null,
                    'feature_name' => $feature['name'],
                    'subject' => $subject,
                    'preview_text' => $previewText,
                    'content_html' => $renderedHtml,
                    'prompt_used' => $campaignData['prompt_used'] ?? null,
                    'llm_model_used' => $campaignData['llm_used'] ?? null,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $sentCount++;
                $details[] = [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'recipient' => $adminUser->email,
                    'feature' => $feature['name'],
                    'status' => 'sent',
                ];
            } catch (\Throwable $e) {
                Log::error('Failed to send campaign email to tenant', [
                    'tenant_id' => $tenant->id,
                    'email' => $adminUser->email,
                    'error' => $e->getMessage(),
                ]);

                CampaignLog::create([
                    'batch_id' => $batchId,
                    'tenant_id' => $tenant->id,
                    'user_id' => $adminUser->id,
                    'recipient_email' => $adminUser->email,
                    'recipient_name' => $adminUser->name,
                    'business_category' => $tenant->business_category ?? 'garment',
                    'feature_key' => $feature['key'] ?? null,
                    'feature_name' => $feature['name'],
                    'subject' => $subject,
                    'preview_text' => $previewText,
                    'content_html' => $renderedHtml,
                    'prompt_used' => $campaignData['prompt_used'] ?? null,
                    'llm_model_used' => $campaignData['llm_used'] ?? null,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                $failedCount++;
                $details[] = [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'recipient' => $adminUser->email,
                    'feature' => $feature['name'],
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'batch_id' => $batchId,
            'total' => $tenants->count(),
            'sent' => $sentCount,
            'failed' => $failedCount,
            'skipped' => $skippedCount,
            'details' => $details,
        ];
    }

    /**
     * Send a single sample test email to a designated address
     *
     * @return array{success: bool, message: string, subject?: string, feature?: string}
     */
    public function sendTestEmail(string $targetEmail, string $category = 'garment', ?string $featureKey = null): array
    {
        $features = FabrikuFeatureCatalog::getFeaturesForCategory($category);

        $selectedFeature = null;
        if ($featureKey) {
            foreach ($features as $f) {
                if ($f['key'] === $featureKey) {
                    $selectedFeature = $f;
                    break;
                }
            }
        }

        if (! $selectedFeature && ! empty($features)) {
            $selectedFeature = $features[0];
        }

        if (! $selectedFeature) {
            return ['success' => false, 'message' => 'Fitur untuk kategori tersebut tidak ditemukan.'];
        }

        // Mock Tenant & User for test preview
        $mockTenant = new Tenant([
            'name' => 'Bisnis Demo ' . ($selectedFeature['category_label'] ?? 'Fabriku'),
            'business_category' => $category,
        ]);
        $mockTenant->id = 0;

        $mockUser = new User([
            'name' => 'Admin Penguji',
            'email' => $targetEmail,
        ]);
        $mockUser->id = 0;

        $campaignData = $this->llmService->generateCampaignContent($mockTenant, $mockUser, $selectedFeature);
        $subject = "[TEST] " . ($campaignData['subject'] ?? $selectedFeature['name']);
        $campaignData['subject'] = $subject;

        try {
            $renderedHtml = view('emails.feature-campaign', [
                'tenant' => $mockTenant,
                'adminUser' => $mockUser,
                'campaignData' => $campaignData,
            ])->render();

            Mail::to($targetEmail)->send(new FeatureCampaignEmail($mockTenant, $mockUser, $campaignData));

            // Record test log
            CampaignLog::create([
                'batch_id' => 'test_' . now()->format('Ymd_His'),
                'tenant_id' => null,
                'user_id' => null,
                'recipient_email' => $targetEmail,
                'recipient_name' => 'Admin Penguji (Test)',
                'business_category' => $category,
                'feature_key' => $selectedFeature['key'] ?? null,
                'feature_name' => $selectedFeature['name'],
                'subject' => $subject,
                'preview_text' => Str::limit($campaignData['intro'] ?? '', 150),
                'content_html' => $renderedHtml,
                'prompt_used' => $campaignData['prompt_used'] ?? null,
                'llm_model_used' => $campaignData['llm_used'] ?? null,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Email uji coba berhasil dikirim ke {$targetEmail}.",
                'subject' => $subject,
                'feature' => $selectedFeature['name'],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengirim email uji coba: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resend an existing campaign log
     */
    public function resendLog(CampaignLog $log): bool
    {
        try {
            Mail::html($log->content_html, function ($message) use ($log) {
                $message->to($log->recipient_email, $log->recipient_name)
                    ->subject($log->subject);
            });

            $log->update([
                'status' => 'sent',
                'error_message' => null,
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
