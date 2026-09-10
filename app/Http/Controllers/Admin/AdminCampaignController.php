<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Campaign\CampaignLlmService;
use App\Services\Campaign\CampaignSenderService;
use App\Services\Campaign\FabrikuFeatureCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCampaignController extends Controller
{
    public function __construct(
        protected CampaignLlmService $llmService,
        protected CampaignSenderService $senderService
    ) {}

    /**
     * Display campaign tracking list and LLM settings
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $category = $request->input('category');

        $query = CampaignLog::with(['tenant', 'user'])
            ->latest('id');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('recipient_email', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('feature_name', 'like', "%{$search}%")
                    ->orWhere('batch_id', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        if (! empty($category)) {
            $query->where('business_category', $category);
        }

        $logs = $query->paginate(15)->withQueryString();

        // High-level statistics
        $stats = [
            'total_sent' => CampaignLog::where('status', 'sent')->count(),
            'total_failed' => CampaignLog::where('status', 'failed')->count(),
            'tenants_reached' => CampaignLog::where('status', 'sent')->whereNotNull('tenant_id')->distinct('tenant_id')->count('tenant_id'),
            'total_unsubscribed' => User::whereNotNull('campaign_unsubscribed_at')->count(),
            'last_sent_at' => CampaignLog::where('status', 'sent')->latest('sent_at')->value('sent_at'),
        ];

        // LLM & Campaign settings
        $apiKey = SystemSetting::get('llm_api_key', config('services.openai.api_key'));
        $settings = [
            'llm_base_url' => SystemSetting::get('llm_base_url', config('services.openai.base_url', 'https://api.openai.com/v1')),
            'llm_model' => SystemSetting::get('llm_model', config('services.openai.model', 'gpt-4o-mini')),
            'llm_temperature' => (float) SystemSetting::get('llm_temperature', 0.7),
            'llm_max_tokens' => (int) SystemSetting::get('llm_max_tokens', 1200),
            'has_api_key' => ! empty($apiKey),
            'campaign_is_active' => (bool) SystemSetting::get('campaign_is_active', true),
            'campaign_system_prompt' => (string) SystemSetting::get('campaign_system_prompt', $this->llmService->getSystemPrompt()),
            'schedule_info' => 'Setiap Hari Senin, Jam 10:00 WIB (Asia/Jakarta)',
        ];

        $categories = config('business.categories', []);

        return Inertia::render('Admin/Campaigns/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'category' => $category ?? '',
            ],
            'stats' => $stats,
            'settings' => $settings,
            'features' => FabrikuFeatureCatalog::all(),
            'categories' => $categories,
        ]);
    }

    /**
     * Update LLM and campaign settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'llm_base_url' => 'required|url',
            'llm_api_key' => 'nullable|string|max:500',
            'llm_model' => 'required|string|max:100',
            'llm_temperature' => 'required|numeric|min:0|max:2',
            'llm_max_tokens' => 'required|integer|min:100|max:8000',
            'campaign_is_active' => 'required|boolean',
            'campaign_system_prompt' => 'nullable|string|max:2000',
        ]);

        SystemSetting::set('llm_base_url', $request->llm_base_url, 'string');
        SystemSetting::set('llm_model', $request->llm_model, 'string');
        SystemSetting::set('llm_temperature', $request->llm_temperature, 'number');
        SystemSetting::set('llm_max_tokens', $request->llm_max_tokens, 'number');
        SystemSetting::set('campaign_is_active', (bool) $request->campaign_is_active, 'boolean');

        if ($request->filled('llm_api_key')) {
            SystemSetting::set('llm_api_key', $request->llm_api_key, 'string');
        }

        if ($request->has('campaign_system_prompt')) {
            SystemSetting::set('campaign_system_prompt', $request->campaign_system_prompt, 'string');
        }

        return redirect()->back()->with('success', 'Pengaturan LLM & Campaign berhasil disimpan.');
    }

    /**
     * Test connection to LLM endpoint
     */
    public function testLlm(Request $request): JsonResponse
    {
        try {
            $override = [];
            if ($request->filled('llm_base_url')) {
                $override['llm_base_url'] = $request->llm_base_url;
            }
            if ($request->filled('llm_api_key')) {
                $override['llm_api_key'] = $request->llm_api_key;
            }
            if ($request->filled('llm_model')) {
                $override['llm_model'] = $request->llm_model;
            }

            $result = $this->llmService->testConnection($override);

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                'latency_ms' => 0,
            ]);
        }
    }

    /**
     * Send a single sample test campaign email
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'category' => 'required|string',
            'feature_key' => 'nullable|string',
        ]);

        $result = $this->senderService->sendTestEmail(
            $request->email,
            $request->category,
            $request->feature_key
        );

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Manually trigger weekly campaign batch right now
     */
    public function sendBatchNow(Request $request): RedirectResponse
    {
        $result = $this->senderService->sendWeeklyCampaign(force: true);

        return redirect()->back()->with(
            'success',
            "Pengiriman kampanye batch selesai. Berhasil: {$result['sent']} email, Gagal: {$result['failed']}, Dilewati: {$result['skipped']}."
        );
    }

    /**
     * View detail of a campaign log (for modal preview)
     */
    public function show(int $id): JsonResponse
    {
        $log = CampaignLog::with(['tenant', 'user'])->findOrFail($id);

        return response()->json([
            'id' => $log->id,
            'batch_id' => $log->batch_id,
            'tenant_name' => $log->tenant?->name ?? 'N/A',
            'recipient_email' => $log->recipient_email,
            'recipient_name' => $log->recipient_name,
            'business_category' => $log->business_category,
            'feature_name' => $log->feature_name,
            'subject' => $log->subject,
            'content_html' => $log->content_html,
            'llm_model_used' => $log->llm_model_used,
            'status' => $log->status,
            'error_message' => $log->error_message,
            'sent_at' => $log->sent_at?->format('d M Y H:i:s'),
            'created_at' => $log->created_at->format('d M Y H:i:s'),
        ]);
    }

    /**
     * Resend a single campaign log
     */
    public function resend(int $id): RedirectResponse
    {
        $log = CampaignLog::findOrFail($id);

        $success = $this->senderService->resendLog($log);

        if ($success) {
            return redirect()->back()->with('success', "Email kampanye berhasil dikirim ulang ke {$log->recipient_email}.");
        }

        return redirect()->back()->with('error', "Gagal mengirim ulang email kampanye ke {$log->recipient_email}.");
    }
}
