@extends('admin.layouts.common')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <h4 class="py-3 mb-4">
                <span class="text-muted fw-light">Transaction /</span> List
            </h4>
        </div>
        <div class="col-md-6 text-end">
            {{-- <a href="{{ route('faq.create') }}" class="btn btn-primary">Add FAQ</a> --}}
        </div>
    </div>
    <div id="flash-messages">
        <x-success-message />
    </div>
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <h5 class="card-header">Transaction List</h5>
        <div class="card-body">
            <table class="datatables-ajax table table-bordered" id="transactionTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order Id</th>
                        <th>Order Payment Status</th>
                        <th>MerchMake Order Id</th>
                        <th>Transaction Id</th>
                        <th>Stripe Charge Id</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
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

                var table = $('#transactionTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('transactions.index') }}",
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
                            data: 'order_payment_status',
                            name: 'order_payment_status'
                        },
                        {
                            data: 'merchmake_order_id',
                            name: 'merchmake_order_id'
                        },
                        {
                            data: 'transaction_id',
                            name: 'transaction_id'
                        },
                        {
                            data: 'charge_id',
                            name: 'charge_id'
                        },
                        {
                            data: 'payment_method',
                            name: 'payment_method'
                        },
                        {
                            data: 'amount',
                            name: 'amount'
                        },
                    ]
                });

            });
        </script>
    @endpush
@endsection
