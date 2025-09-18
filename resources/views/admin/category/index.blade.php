@extends('admin.layouts.common')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <h4 class="py-3 mb-4">
                <span class="text-muted fw-light">Category /</span> List
            </h4>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('category.create') }}" class="btn btn-primary">Add Category</a>
        </div>
    </div>
    <div id="flash-messages">
        <x-success-message />



    </div>
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <h5 class="card-header">Category List</h5>
        <div class="card-body">
            <table class="datatables-ajax table table-bordered" id="categoryTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Image</th>
                        <th>MerchMake Category Id</th>
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

                var table = $('#categoryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('category.index') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'image',
                            name: 'image'
                        },
                        {
                            data: 'category_id',
                            name: 'category_id'
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
