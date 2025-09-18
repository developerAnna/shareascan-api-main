<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\MerchMake;

class UpdateOrderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateorder:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Order Status Based on Merchmake Response';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("cron run for update order status");
        $merchmake = new Merchmake;
        $orders = Order::where('status', 1)->where('order_status', '!=', 'Completed')->get();
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                $merchmake_order = $merchmake->getOrder($order->merchmake_order_id);
                if ($merchmake_order === false) {
                    Log::error("Error fetching order from Merchmake for Order ID: {$order->id}");
                } else {
                    Log::info($merchmake_order['order']['status']);
                    $order->update(['merchmake_order_status' => $merchmake_order['order']['status']]);
                }
            }
        }
    }
}
