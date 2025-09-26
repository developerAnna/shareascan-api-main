@extends('admin.layouts.common')
@section('content')
    <div class="row g-4 mb-4">
        <!-- Total Sales Card -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Sales</span>
                            <div class="d-flex align-items-end mt-2">
                                <h3 class="mb-0 me-2">
                                    {{ isset($total_sales) ? '$ ' . number_format($total_sales, 2) : '$ 0.00' }}</h3>
                            </div>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="bx bx-wallet bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Card -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Current Day Sales</span>
                            <div class="d-flex align-items-end mt-2">
                                <h3 class="mb-0 me-2">
                                    {{ isset($current_day_sales) ? '$ ' . number_format($current_day_sales, 2) : '$ 0.00' }}
                                </h3>
                            </div>
                        </div>
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="bx bx-cart"></i> <!-- Money icon -->
                        </span>
                    </div>
                </div>
            </div>
        </div>


        <!-- Total Orders Card -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Orders</span>
                            <div class="d-flex align-items-end mt-2">
                                <h3 class="mb-0 me-2">{{ isset($total_orders) ? $total_orders : '0' }}</h3>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="bx bx-package bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>



        <!-- Total Customers Card -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Customers</span>
                            <div class="d-flex align-items-end mt-2">
                                <h3 class="mb-0 me-2">{{ isset($total_users) ? $total_users : '0' }}</h3>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="bx bx-group bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
