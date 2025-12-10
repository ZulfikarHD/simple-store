<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\StoreSettingService;
use Illuminate\Console\Command;

/**
 * Command untuk auto-cancel pending orders yang melewati batas waktu
 * Dijalankan via scheduler setiap menit untuk mengecek orders yang expired
 *
 * @author Zulfikar Hidayatullah
 */
class AutoCancelPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-cancel
                            {--dry-run : Tampilkan orders yang akan di-cancel tanpa eksekusi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel pending orders yang melewati batas waktu yang ditentukan di settings';

    /**
     * Execute the console command.
     */
    public function handle(StoreSettingService $settingService): int
    {
        // Cek apakah fitur auto-cancel diaktifkan
        if (! $settingService->isAutoCancelEnabled()) {
            $this->info('Auto-cancel fitur dinonaktifkan di settings.');

            return self::SUCCESS;
        }

        $minutes = $settingService->getAutoCancelMinutes();
        $cutoffTime = now()->subMinutes($minutes);

        $this->info("Mencari pending orders yang dibuat sebelum {$cutoffTime->format('Y-m-d H:i:s')} ({$minutes} menit lalu)...");

        // Query pending orders yang sudah melewati batas waktu
        $expiredOrders = Order::query()
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('Tidak ada pending orders yang perlu di-cancel.');

            return self::SUCCESS;
        }

        $this->info("Ditemukan {$expiredOrders->count()} order(s) yang akan di-cancel.");

        // Dry run mode - hanya tampilkan tanpa eksekusi
        if ($this->option('dry-run')) {
            $this->table(
                ['Order Number', 'Customer', 'Total', 'Created At', 'Waiting Time'],
                $expiredOrders->map(fn ($order) => [
                    $order->order_number,
                    $order->customer_name,
                    'Rp '.number_format($order->total, 0, ',', '.'),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->created_at->diffForHumans(),
                ])
            );
            $this->warn('Dry run mode: Tidak ada order yang di-cancel.');

            return self::SUCCESS;
        }

        // Cancel setiap order yang expired
        $cancelledCount = 0;
        foreach ($expiredOrders as $order) {
            $order->cancel("Auto-cancelled: Tidak dikonfirmasi dalam {$minutes} menit");
            $cancelledCount++;

            $this->line("  ✓ {$order->order_number} - {$order->customer_name}");
        }

        $this->info("Berhasil cancel {$cancelledCount} order(s).");

        return self::SUCCESS;
    }
}
