@extends('admin.layouts.common')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <h4 class="py-3 mb-4">
                <span class="text-muted fw-light">Email Template /</span> List
            </h4>
        </div>
        {{-- <div class="col-md-6 text-end">
            <a href="{{ route('email-templates.create') }}" class="btn btn-primary">Add Email Template</a>
        </div> --}}
    </div>
    <div id="flash-messages">
        <x-success-message />
    </div>
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <h5 class="card-header">Email Template List</h5>
        <div class="card-body">
            <table class="datatables-ajax table table-bordered" id="EmailTemplateTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Subject</th>
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

                var table = $('#EmailTemplateTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('email-templates.index') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'subject',
                            name: 'subject'
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
