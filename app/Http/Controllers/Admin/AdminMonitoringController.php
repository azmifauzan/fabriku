<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\ScheduleLog;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminMonitoringController extends Controller
{
    /**
     * Display the monitoring dashboard
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'overview');

        return Inertia::render('Admin/Monitoring/Index', [
            'tab' => $tab,
            'overview' => $this->getOverviewData(),
            'schedules' => fn () => $this->getScheduleData($request),
            'emails' => fn () => $this->getEmailData($request),
            'jobs' => fn () => $this->getJobsData(),
            'server' => fn () => $this->getServerData(),
        ]);
    }

    /**
     * Get overview statistics
     */
    private function getOverviewData(): array
    {
        return [
            'queue_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'emails_today' => EmailLog::whereDate('created_at', today())->count(),
            'emails_failed' => EmailLog::status('failed')->whereDate('created_at', today())->count(),
            'schedules_running' => ScheduleLog::status('running')->count(),
            'schedules_failed_today' => ScheduleLog::status('failed')->whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Get schedule logs data
     */
    private function getScheduleData(Request $request): array
    {
        $logs = ScheduleLog::query()
            ->when($request->filled('command'), fn ($q) => $q->where('command', 'like', '%'.$request->command.'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Get unique commands for filter
        $commands = ScheduleLog::select('command')
            ->distinct()
            ->pluck('command');

        // Get scheduled tasks configuration
        $scheduledTasks = $this->getScheduledTasks();

        return [
            'logs' => $logs,
            'commands' => $commands,
            'scheduled_tasks' => $scheduledTasks,
            'filters' => $request->only(['command', 'status']),
        ];
    }

    /**
     * Get configured scheduled tasks
     */
    private function getScheduledTasks(): array
    {
        return [
            [
                'command' => 'demo:reset',
                'schedule' => 'Hourly',
                'description' => 'Reset demo data',
            ],
            [
                'command' => 'trial:send-reminders',
                'schedule' => 'Daily at 09:00',
                'description' => 'Send trial expiry reminders',
            ],
        ];
    }

    /**
     * Get email logs data
     */
    private function getEmailData(Request $request): array
    {
        $logs = EmailLog::query()
            ->with(['tenant:id,name', 'user:id,name,email'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($query) use ($search) {
                    $query->where('to_email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => EmailLog::count(),
            'sent' => EmailLog::status('sent')->count(),
            'failed' => EmailLog::status('failed')->count(),
            'sending' => EmailLog::status('sending')->count(),
        ];

        return [
            'logs' => $logs,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ];
    }

    /**
     * Get queue jobs data
     */
    private function getJobsData(): array
    {
        $pendingJobs = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as count'))
            ->groupBy('queue')
            ->get();

        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(20)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);

                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'job_name' => $payload['displayName'] ?? 'Unknown',
                    'failed_at' => $job->failed_at,
                    'exception' => Str::limit($job->exception, 500),
                ];
            });

        return [
            'pending' => $pendingJobs,
            'failed' => $failedJobs,
            'total_pending' => DB::table('jobs')->count(),
            'total_failed' => DB::table('failed_jobs')->count(),
        ];
    }

    /**
     * Get server resource data
     */
    private function getServerData(): array
    {
        $serverData = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
        ];

        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $serverData['memory'] = [
            'current' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes($memoryPeak),
            'limit' => ini_get('memory_limit'),
        ];

        // Database connection check
        try {
            DB::connection()->getPdo();
            $serverData['database'] = [
                'status' => 'connected',
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            $serverData['database'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }

        // Disk space (if accessible)
        if (function_exists('disk_free_space')) {
            $storagePath = storage_path();
            $freeSpace = @disk_free_space($storagePath);
            $totalSpace = @disk_total_space($storagePath);

            if ($freeSpace !== false && $totalSpace !== false) {
                $serverData['disk'] = [
                    'free' => $this->formatBytes($freeSpace),
                    'total' => $this->formatBytes($totalSpace),
                    'used_percent' => round((($totalSpace - $freeSpace) / $totalSpace) * 100, 1),
                ];
            }
        }

        // Cache check
        try {
            Cache::put('health_check', true, 10);
            $serverData['cache_status'] = Cache::get('health_check') ? 'working' : 'error';
        } catch (\Exception $e) {
            $serverData['cache_status'] = 'error';
        }

        return $serverData;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Retry a failed job
     */
    public function retryJob(Request $request, string $uuid)
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('success', 'Job has been queued for retry');
    }

    /**
     * Delete a failed job
     */
    public function deleteJob(Request $request, string $uuid)
    {
        DB::table('failed_jobs')->where('uuid', $uuid)->delete();

        return back()->with('success', 'Failed job has been deleted');
    }

    /**
     * Retry all failed jobs
     */
    public function retryAllJobs()
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'All failed jobs have been queued for retry');
    }

    /**
     * Flush all failed jobs
     */
    public function flushJobs()
    {
        Artisan::call('queue:flush');

        return back()->with('success', 'All failed jobs have been deleted');
    }

    /**
     * Run a scheduled command manually
     */
    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => ['required', 'string'],
        ]);

        $command = $request->command;
        $log = ScheduleLog::start($command);

        try {
            Artisan::call($command);
            $output = Artisan::output();
            $log->markCompleted($output);

            return back()->with('success', "Command '{$command}' executed successfully");
        } catch (\Exception $e) {
            $log->markFailed($e->getMessage());

            return back()->with('error', "Command failed: {$e->getMessage()}");
        }
    }

    /**
     * Send a test Telegram notification
     */
    public function testTelegram()
    {
        $telegram = app(TelegramService::class);

        if (! $telegram->isConfigured()) {
            return back()->with('error', 'Telegram is not configured');
        }

        $result = $telegram->sendAdminNotification(
            "✅ <b>Test Notification</b>\n\n".
            "This is a test message from Fabriku Admin Panel.\n".
            'Sent at: '.now()->format('d M Y H:i:s')
        );

        if ($result['success']) {
            return back()->with('success', 'Test notification sent successfully');
        }

        return back()->with('error', 'Failed to send: '.($result['error'] ?? 'Unknown error'));
    }
}
