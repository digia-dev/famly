<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetAiUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fams:reset-ai-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset AI usage count for all users daily at midnight.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting AI usage counts...');
        
        User::query()->update(['ai_usage_count' => 0]);
        
        $this->info('AI usage counts have been successfully reset.');
    }
}
