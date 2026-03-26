<?php
namespace App\Console\Commands;

use App\Services\Payment\RazorpayService;
use Illuminate\Console\Command;

class ProcessSettlementsCommand extends Command
{
    protected $signature   = 'settlements:process';
    protected $description = 'Move 48-hour held payments from pending to seller wallets';

    public function handle(RazorpayService $razorpay): int
    {
        $count = $razorpay->processSettlements();
        $this->info("Settled {$count} purchase(s) to seller wallets.");
        return Command::SUCCESS;
    }
}
