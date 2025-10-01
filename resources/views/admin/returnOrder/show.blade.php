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
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Return Order/</span>
        Show
    </h4>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Return Order Details </h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Order Id</th>
                            <td>{{ $return_order->order_id }}</td>
                        </tr>
                        <tr>
                            <th>Merchmake Order Id</th>
                            <td>{{ $return_order->order->merchmake_order_id }}</td>
                        </tr>
                        <tr>
                            <th>Reason</th>
                            <td>{{ $return_order->reason }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $return_order->description }}</td>
                        </tr>
                        <tr>
                            <th>Return Status</th>
                            <td>
                                @php
                                    $status = $return_order->return_status ?? '';
                                    $badgeClass = 'bg-label-secondary'; // Default

                                    if ($status === 'Refunded') {
                                        $badgeClass = 'bg-label-success';
                                    } elseif ($status === 'Pending') {
                                        $badgeClass = 'bg-label-primary';
                                    } elseif ($status === 'Processing') {
                                        $badgeClass = 'bg-label-warning';
                                    } elseif ($status === 'Canceled') {
                                        $badgeClass = 'bg-label-danger';
                                    }
                                @endphp

                                <span class="badge badge-dim {{ $badgeClass }}">{{ $status }}</span>
                            </td>
                        </tr>

                        <tr>
                            <th>Send to MerchMake</th>
                            <td>
                                <span class="@if ($return_order->is_send_to_merchmake == 1) text-success @else text-muted @endif">
                                    {{ $return_order->is_send_to_merchmake == 1 ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                        @if (isset($return_order->returnOrderImages) && !empty($return_order->returnOrderImages))
                            <tr>
                                <th>Images</th>
                                <td>
                                    <div class="image-gallery">
                                        <div class="image-grid">
                                            @foreach ($return_order->returnOrderImages as $return_img)
                                                <div class="image-item mt-3">
                                                    <img src="{{ asset('storage/' . $return_img->image_path) }}"
                                                        alt="Return Order Image" width="200" height="200" />
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </table>
                    <div class="text-center mt-3">
                        <a href="{{ route('return-orders.index') }}" class="btn btn-secondary"
                            style="display: inline-block; margin-right: 10px;">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            function sendReturnRequest() {
                // Here you can add the logic for sending the return request
                alert('Return request sent to MerchMake!');
                // Make an API request or route call if needed
            }
        </script>
    @endpush
@endsection
