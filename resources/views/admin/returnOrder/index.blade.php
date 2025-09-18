@extends('admin.layouts.common')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <h4 class="py-3 mb-4">
                <span class="text-muted fw-light">Return Orders /</span> List
            </h4>
        </div>
    </div>
    <div id="flash-messages">
        <x-success-message />
    </div>
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <h5 class="card-header">Return Orders List</h5>
        <div class="card-body">
            <table class="datatables-ajax table table-bordered" id="returnORderTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order Id</th>
                        <th>User Name</th>
                        <th>User Email</th>
                        <th>Reason</th>
                        <th>Return Status</th>
                        <th>Is sent to MerchMake</th>
                        <th>Refund</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('admin/assets/js/send-merchmake-request.js') }}"></script>

        <script>
            $(document).ready(function() {

                var table = $('#returnORderTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('return-orders.index') }}",
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
                            data: 'user_name',
                            name: 'user_name'
                        },
                        {
                            data: 'user_email',
                            name: 'user_email'
                        },
                        {
                            data: 'reason',
                            name: 'reason'
                        },
                        {
                            data: 'return_status',
                            name: 'return_status'
                        },
                        {
                            data: 'is_send_to_merchmake',
                            name: 'is_send_to_merchmake'
                        },
                        {
                            data: 'refund',
                            name: 'refund'
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
