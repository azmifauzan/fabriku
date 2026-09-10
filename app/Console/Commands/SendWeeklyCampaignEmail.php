<?php

namespace App\Console\Commands;

use App\Services\Campaign\CampaignSenderService;
use Illuminate\Console\Command;

class SendWeeklyCampaignEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:send-weekly 
                            {--tenant= : ID Tenant tertentu yang ingin dikirimkan} 
                            {--dry-run : Simulasi pembuatan konten tanpa mengirim email} 
                            {--force : Paksa pengiriman meski pengaturan kampanye dinonaktifkan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim campaign email mingguan berisikan fitur Fabriku ke admin tenant';

    /**
     * Execute the console command.
     */
    public function handle(CampaignSenderService $senderService): int
    {
        $tenantId = $this->option('tenant') ? (int) $this->option('tenant') : null;
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->info('Memulai proses pengiriman campaign email mingguan ke admin tenant...');
        if ($dryRun) {
            $this->warn('Mode DRY-RUN aktif. Email tidak akan dikirimkan ke penerima.');
        }

        $result = $senderService->sendWeeklyCampaign($tenantId, $dryRun, $force);

        if (isset($result['message']) && empty($result['batch_id'])) {
            $this->warn($result['message']);
            return Command::SUCCESS;
        }

        $this->table(
            ['Batch ID', 'Total Tenant', 'Berhasil Dikirim', 'Gagal', 'Dilewati'],
            [[
                $result['batch_id'],
                $result['total'],
                $result['sent'],
                $result['failed'],
                $result['skipped'],
            ]]
        );

        $this->info("Pengiriman selesai: {$result['sent']} email berhasil diproses.");

        return $result['failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
