<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Order;
use App\Services\OrderInMerchmakeService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandlePaypalSaleCompleted implements ShouldQueue
{
    use Queueable,Dispatchable, InteractsWithQueue,SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $merchMakeService = new OrderInMerchmakeService();
        $merchMakeService->handlePaymentSucceed($this->order);
    }
}
