@extends('admin.layouts.common')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <h4 class="py-3 mb-4">
                <span class="text-muted fw-light">Review /</span> List
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
        <h5 class="card-header">Review List</h5>
        <div class="card-body">
            <table class="datatables-ajax table table-bordered" id="reviewTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Merchmake Product Id</th>
                        <th>Product Title</th>
                        <th>User Name</th>
                        <th>Status</th>
                        <th>Rating</th>
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

                var table = $('#reviewTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('review.index') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'product_id',
                            name: 'product_id'
                        },
                        {
                            data: 'product_title',
                            name: 'product_title'
                        },
                        {
                            data: 'user_id',
                            name: 'user_id'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'star_count',
                            name: 'star_count'
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
