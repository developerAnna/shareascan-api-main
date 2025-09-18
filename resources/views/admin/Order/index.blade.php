@extends('admin.layouts.common')
@section('content')
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @endpush
    <div class="row">
        <div class="col-md-6">
            <h4 class="py-3 mb-4">
                <span class="text-muted fw-light">Orders /</span> List
            </h4>
        </div>
        <div class="col-md-6 text-end">
        </div>
    </div>
    <div id="flash-messages">
        <x-success-message />
    </div>
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <h5 class="card-header">Orders List</h5>
        <div class="card-body">
            <table class="datatables-ajax table table-bordered" id="orderTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order Id</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>MerchMake Order Id</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('admin/assets/js/delete-records.js') }}"></script>

        <script>
            $(document).ready(function() {

                var table = $('#orderTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('orders.index') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'order_id',
                            name: 'order_id'
                        },
                        {
                            data: 'user_id',
                            name: 'user_id'
                        },
                        {
                            data: 'total',
                            name: 'total'
                        },
                        {
                            data: 'payment_method',
                            name: 'payment_method'
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status'
                        },
                        {
                            data: 'order_status',
                            name: 'order_status'
                        },
                        {
                            data: 'merchmake_order_id',
                            name: 'merchmake_order_id'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ]
                });

            });
        </script>
    @endpush
@endsection
