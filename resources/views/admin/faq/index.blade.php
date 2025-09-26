@extends('admin.layouts.common')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <h4 class="py-3 mb-4">
                <span class="text-muted fw-light">FAQ /</span> List
            </h4>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('faq.create') }}" class="btn btn-primary">Add FAQ</a>
        </div>
    </div>
    <div id="flash-messages">
        <x-success-message />
    </div>
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <h5 class="card-header">FAQ List</h5>
        <div class="card-body">
            <table class="datatables-ajax table table-bordered" id="faqTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Question</th>
                        <th>Status</th>
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

                var table = $('#faqTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('faq.index') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'question',
                            name: 'question'
                        },
                        {
                            data: 'status',
                            name: 'status'
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
