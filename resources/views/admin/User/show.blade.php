@extends('admin.layouts.common')
@section('content')
    @push('css')
        <style>
            .table th,
            .table td {
                padding: 15px;
                vertical-align: middle;
            }

            .badge {
                padding: 6px 12px;
                font-size: 14px;
                border-radius: 12px;
                color: #fff;
            }

            .bg-success {
                background-color: #28a745;
            }

            .bg-warning {
                background-color: #ffc107;
            }

            .bg-danger {
                background-color: #dc3545;
            }

            .text-success {
                color: #28a745;
            }

            .text-muted {
                color: #6c757d;
            }

            .image-grid {
                display: flex;
                gap: 10px;
            }

            .image-item img {
                width: 100px;
                height: 100px;
                border-radius: 6px;
                border: 1px solid #ddd;
                object-fit: cover;
            }

            .btn-secondary,
            .btn-primary {
                padding: 10px 20px;
                font-size: 16px;
                border-radius: 6px;
                text-decoration: none;
                cursor: pointer;
            }

            .btn-secondary {
                color: #fff;
                background-color: #6c757d;
            }

            .btn-secondary:hover {
                background-color: #5a6268;
            }

            .btn-primary {
                color: #fff;
                background-color: #007bff;
            }

            .btn-primary:hover {
                background-color: #0056b3;
            }
        </style>
    @endpush
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">User/</span>
        Show
    </h4>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Details </h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>First Name & Last Name</th>
                            <td>{{ $user->name . ' ' . $user->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td>{{ isset($user->user_details) ? $user->user_details->phone_number : '' }}</td>
                        </tr>

                        <tr>
                            <th>Address Line 1</th>
                            <td>{{ isset($user->user_details) ? $user->user_details->address_line_1 : '' }}</td>
                        </tr>
                        <tr>
                            <th>Address Line 2</th>
                            <td>{{ isset($user->user_details) ? $user->user_details->address_line_2 : '' }}</td>
                        </tr>
                        <tr>
                            <th>City</th>
                            <td>{{ isset($user->user_details) ? $user->user_details->city : '' }}</td>
                        </tr>
                        <tr>
                            <th>State</th>
                            <td>{{ isset($user->user_details) ? $user->user_details->state : '' }}</td>
                        </tr>
                        <tr>
                            <th>Country</th>
                            <td>{{ isset($user->user_details) ? $user->user_details->country : '' }}</td>
                        </tr>
                        <tr>
                            <th>ZipCode</th>
                            <td>{{ isset($user->user_details) ? $user->user_details->zipcode : '' }}</td>
                        </tr>

                    </table>
                    <div class="text-center mt-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary"
                            style="display: inline-block; margin-right: 10px;">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')

    @endpush
@endsection
