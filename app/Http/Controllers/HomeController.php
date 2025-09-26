<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function adminDashboard()
    {
        $data['total_users'] = User::count();
        $data['total_orders'] = Order::where('status', 1)->count();
        $data['current_day_sales'] = Order::where('status', 1)->where('payment_status', 'Completed')
            ->whereDate('created_at', Carbon::today())
            ->sum('total');

        $data['total_sales'] = Order::where('status', 1)->where('payment_status', 'Completed')->sum('total');

        return view('admin.dashboard')->with($data);
    }
}
