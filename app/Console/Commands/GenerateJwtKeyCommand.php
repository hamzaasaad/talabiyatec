<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateJwtKeyCommand extends Command
{
    /**
     * اسم الأمر الذي سينفذ من الطرفية.
     */
    protected $signature = 'jwt:generate-key {--force : Force regeneration even if key exists}';

    
    protected $description = '🔐 Generate a secure JWT secret and store it in the .env file';

    /**
     * تنفيذ الأمر.
     */
    public function handle(): int
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->error('❌ No .env file found at project root.');
            return Command::FAILURE;
        }

        $envContent = file_get_contents($envPath);

        if (preg_match('/^JWT_SECRET=.*$/m', $envContent)) {
            if (!$this->option('force')) {
                $this->warn('⚠️  JWT_SECRET already exists. Use --force to regenerate.');
                return Command::SUCCESS;
            }

            $this->warn('⚠️  Overwriting existing JWT_SECRET...');
        }

        $newKey = 'base64:' . base64_encode(random_bytes(64));

        if (preg_match('/^JWT_SECRET=.*$/m', $envContent)) {
            $envContent = preg_replace('/^JWT_SECRET=.*$/m', "JWT_SECRET={$newKey}", $envContent);
        } else {
            $envContent .= "\nJWT_SECRET={$newKey}\n";
        }

        file_put_contents($envPath, $envContent);

        $this->info("✅ New JWT secret generated and stored in .env");
        $this->line("🔑 Your new key: {$newKey}");

        return Command::SUCCESS;
    }
}
