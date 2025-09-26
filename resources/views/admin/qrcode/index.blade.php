@extends('admin.layouts.common')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <h4 class="py-3 mb-4">
                <span class="text-muted fw-light">QR Code /</span> List
            </h4>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('qrcode.create') }}" class="btn btn-primary">Add QR Code</a>
        </div>
    </div>
    <div id="flash-messages">
        <x-success-message />
    </div>
    <!-- Ajax Sourced Server-side -->
    <div class="card">
        <h5 class="card-header">QR Code List</h5>
        <div class="card-body">
            <table class="datatables-ajax table table-bordered" id="qrcodeTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hexa color</th>
                        <th>RGB color</th>
                        <th>QR Data</th>
                        <th>QR Image</th>
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

                var table = $('#qrcodeTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('qrcode.index') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'hexa_color',
                            name: 'hexa_color'
                        },
                        {
                            data: 'rgb_color',
                            name: 'rgb_color'
                        },
                        {
                            data: 'qr_data',
                            name: 'qr_data'
                        },
                        {
                            data: 'qr_image_path',
                            name: 'qr_image_path'
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
