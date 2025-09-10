<?php

namespace App\Console\Commands;

use danog\MadelineProto\API;
use Illuminate\Console\Command;

class TelegramCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to Telegram channel messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sessionFile = storage_path('app/madeline/session.madeline');
        
        // Debug: Check if credentials are loaded
        $this->info("API ID: " . env('TELEGRAM_API_ID'));
        $this->info("API Hash: " . env('TELEGRAM_API_HASH'));

        // Clear existing session file if exists
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
        if (file_exists($sessionFile . '.lock')) {
            unlink($sessionFile . '.lock');
        }

        // Ensure directory exists
        if (!file_exists(dirname($sessionFile))) {
            mkdir(dirname($sessionFile), 0755, true);
        }

        try {
            // Configure API credentials
            $settings = [
                'app_info' => [
                    'api_id' => env('TELEGRAM_API_ID'), // Cast to integer
                    'api_hash' => env('TELEGRAM_API_HASH'),
                ],
                'logger' => [
                    'logger' => 3,
                    'logger_level' => 4
                ]
            ];

            $this->info("Settings: " . json_encode($settings)); // Debug settings

            $madeline = new API($sessionFile, $settings);
            
            // Login (only needed once)
            $phone = '6282116140638'; // Your phone
            $madeline->phoneLogin($phone);
            $code = $this->ask('Enter the login code: ');
            $madeline->completePhoneLogin($code);

            // Start event handler
            $madeline->startAndLoop(\App\Telegram\UpdateHandler::class);
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
