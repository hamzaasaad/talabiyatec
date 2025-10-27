<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Repositories\Interfaces\RefreshTokenRepositoryInterface;
use Carbon\Carbon;

class CleanupExpiredRefreshTokens extends Command
{
    protected $signature = 'refresh-tokens:cleanup';
    protected $description = '🧹 Cleanup expired and old revoked refresh tokens';

    public function handle(RefreshTokenRepositoryInterface $repository): int
    {
        $startTime = microtime(true);
        $timestamp = Carbon::now()->toDateTimeString();

        $expired = $repository->deleteExpired();
        $revoked = $repository->deleteOldRevoked(now()->subDays(30));

        $duration = round(microtime(true) - $startTime, 2);
        $total = $expired + $revoked;

        // 🪵 سجل العملية في ملف maintenance.log
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/maintenance.log'),
        ])->info('🧹 RefreshToken Cleanup Summary', [
            'timestamp' => $timestamp,
            'expired_deleted' => $expired,
            'revoked_deleted' => $revoked,
            'duration_seconds' => $duration,
            'total_deleted' => $total,
        ]);

        $this->info("✅ Cleanup complete! Deleted {$total} tokens ({$duration}s)");

        if ($total > 1000) {
            $this->sendSlackAlert($total, $expired, $revoked, $timestamp);
        }

        return Command::SUCCESS;
    }

    
    protected function sendSlackAlert(int $total, int $expired, int $revoked, string $timestamp): void
    {
        $webhookUrl = config('services.slack.webhook_url') ?? env('SLACK_WEBHOOK_URL');

        if (!$webhookUrl) {
            Log::warning('⚠️ No Slack webhook configured. Skipping Slack alert.');
            return;
        }

        $message = [
            'text' => "🚨 *Unusual Cleanup Activity Detected!*\n"
                . "🧹 *{$total} tokens deleted* at {$timestamp}\n"
                . "• Expired: {$expired}\n"
                . "• Revoked: {$revoked}\n"
                . "Check system health and token revocation behavior.",
        ];

        try {
            Http::post($webhookUrl, $message);
            Log::info("📣 Slack alert sent for large cleanup: {$total} tokens");
        } catch (\Throwable $e) {
            Log::error('❌ Failed to send Slack alert', ['error' => $e->getMessage()]);
        }
    }
}
