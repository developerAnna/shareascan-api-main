@extends('admin.layouts.common')
@section('content')
<style>
    .order-card {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 20px;
        font-family: Arial, sans-serif;
        font-size: 14px;
    }

    .order-card-header {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        padding: 10px 15px;
        font-weight: 600;
    }

    .order-card-table {
        width: 100%;
        border-collapse: collapse;
    }

    .order-card-table th {
        text-align: left;
        padding: 8px 12px;
        font-weight: 600;
        color: inherit;
        width: 40%;
        vertical-align: top;
    }

    .order-card-table td {
        padding: 8px 12px;
        vertical-align: top;
    }

    .order-section {
        width: 50%;
        vertical-align: top;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }


</style>
    <h4 class="py-3 mb-4"><span class="text-muted fw-light">Order/</span>
        Show
    </h4>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order Details </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-4">
                                <div class="">
                                    <!-- Two-column table for order/payment and shipping details -->
                                    <!-- <table class="w-100" style="border-collapse: collapse; width: 100%;">
                                        <tr>
                        
                                            <td style="vertical-align: top; padding-right: 10px;">
                                                <table style="border-collapse: collapse; width: 100%;">
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Order ID:</strong></th>
                                                        <td id="order-id">{{ $order->id }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Merchmake Order ID:</strong>
                                                        </th>
                                                        <td id="merchmake-order-id">{{ $order->merchmake_order_id }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Payment Method:</strong></th>
                                                        <td>{{ $order->payment_method }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Payment Status:</strong></th>
                                                        <td>
                                                            @php
                                                                $status = $order->payment_status ?? '';
                                                                $badgeClass = 'bg-label-primary'; // Default

                                                                if ($status === 'Completed') {
                                                                    $badgeClass = 'bg-label-success';
                                                                } elseif ($status === 'Refunded') {
                                                                    $badgeClass = 'bg-label-warning';
                                                                }
                                                            @endphp

                                                            <span class="badge badge-dim {{ $badgeClass }}">{{ $status }}</span>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th style="text-align: left;"><strong>Order Status:</strong></th>
                                                        <td>
                                                            @php
                                                                $status = $order->order_status ?? '';
                                                                $badgeClass = 'bg-label-secondary'; // Default badge color

                                                                if ($status === 'Completed') {
                                                                    $badgeClass = 'bg-label-success';
                                                                } elseif ($status === 'Pending') {
                                                                    $badgeClass = 'bg-label-primary';
                                                                } elseif (in_array($status, ['Cancelled', 'Refunded'])) {
                                                                    $badgeClass = 'bg-label-danger';
                                                                }
                                                            @endphp

                                                            <span class="badge badge-dim {{ $badgeClass }}">{{ $status }}</span>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <th style="text-align: left;"><strong>Note:</strong></th>
                                                        <td id="order-note">{{ $order->note }}</td>
                                                    </tr>
                                                </table>
                                            </td>

                                            <td style="vertical-align: top; padding-left: 20px;">
                                                <table style="border-collapse: collapse; width: 100%;">
                                                    <tr>
                                                        <th colspan="2" style="text-align: left;"><strong>Shipping
                                                                Address:</strong></th>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Name:</strong></th>
                                                        <td>{{ $order->shippingAddress->first_name . ' ' . $order->shippingAddress->last_name }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Email:</strong></th>
                                                        <td>{{ $order->shippingAddress->email }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Phone:</strong></th>
                                                        <td>{{ $order->shippingAddress->phone }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Address Line 1:</strong></th>
                                                        <td>{{ $order->shippingAddress->address_1 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Address Line 2:</strong></th>
                                                        <td>{{ $order->shippingAddress->address_2 }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align: left;"><strong>Country Code:</strong></th>
                                                        <td>{{ $order->shippingAddress->country_code }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table> -->

                                    <div class="order-card">
                                        <table class="order-card-table">
                                            <tr>
                                                <!-- Left side: Order and Payment Details -->
                                                <td class="order-section">
                                                    <div class="order-card-header">Order & Payment Details</div>
                                                    <table class="order-card-table">
                                                        <tr>
                                                            <th>Order ID:</th>
                                                            <td>{{ $order->id }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Merchmake Order ID:</th>
                                                            <td>{{ $order->merchmake_order_id }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Payment Method:</th>
                                                            <td>{{ $order->payment_method }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Payment Status:</th>
                                                            <td>
                                                                @php
                                                                    $status = $order->payment_status ?? '';
                                                                    $badgeClass = 'bg-label-primary';
                                                                    if ($status === 'Completed') $badgeClass = 'bg-label-success';
                                                                    elseif ($status === 'Refunded') $badgeClass = 'bg-label-warning';
                                                                @endphp
                                                                <span class="badge {{ $badgeClass }}">{{ strtoupper($status) }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Order Status:</th>
                                                            <td>
                                                                @php
                                                                    $status = $order->order_status ?? '';
                                                                    $badgeClass = 'bg-label-secondary';
                                                                    if ($status === 'Completed') $badgeClass = 'bg-label-success';
                                                                    elseif ($status === 'Pending') $badgeClass = 'bg-label-primary';
                                                                    elseif (in_array($status, ['Cancelled','Refunded'])) $badgeClass = 'bg-label-danger';
                                                                @endphp
                                                                <span class="badge {{ $badgeClass }}">{{ strtoupper($status) }}</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Note:</th>
                                                            <td>{{ $order->note }}</td>
                                                        </tr>
                                                    </table>
                                                </td>

                                                <!-- Right side: Shipping Address -->
                                                <td class="order-section">
                                                    <div class="order-card-header">Shipping Address</div>
                                                    <table class="order-card-table">
                                                        <tr>
                                                            <th>Name:</th>
                                                            <td>{{ $order->shippingAddress->first_name . ' ' . $order->shippingAddress->last_name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Email:</th>
                                                            <td>{{ $order->shippingAddress->email }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Phone:</th>
                                                            <td>{{ $order->shippingAddress->phone }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Address Line 1:</th>
                                                            <td>{{ $order->shippingAddress->address_1 }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Address Line 2:</th>
                                                            <td>{{ $order->shippingAddress->address_2 }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Country Code:</th>
                                                            <td>{{ $order->shippingAddress->country_code }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="mb-3">
                                        <h6><strong>Order Items:</strong></h6>
                                        @if (!empty($order->orderItems))
                                            <div style="max-height: auto; overflow-y: auto;">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Product Title</th>
                                                            <th>Merchmake Product Id</th>
                                                            <th>Product Images</th>
                                                            <th>Variation</th>
                                                            <th>Qr Images</th>
                                                            <th>Quantity</th>
                                                            <th>Price</th>
                                                            <th>Sub Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($order->orderItems->groupBy('cart_id') as $cartId => $items)
                                                            @php
                                                                $image_url = getProductImage(
                                                                    $items->first()->product_id,
                                                                    null,
                                                                );
                                                            @endphp
                                                            <tr>
                                                                
                                                                <td>{{ $items->first()->product_title }}</td>

                                                               
                                                                <td>{{ $items->first()->product_id }}</td>

                                                             
                                                                <td>
                                                                    <img src="{{ $image_url['image_url'] }}" width="100"
                                                                        alt="Product Image">
                                                                </td>

                                                             
                                                                <td>{{ $items->first()->variation_color }},
                                                                    {{ $items->first()->variation_size }}</td>

                                                            
                                                                <td>
                                                                    <div class="d-flex flex-wrap">
                                                                        @foreach ($items as $key => $item)
                                                                            @if (!empty($item->getOrderItemQrCodes))
                                                                                @foreach ($item->getOrderItemQrCodes as $qrCode)
                                                                                    <div class="p-2">
                                                                                        <p>{{ $qrCode->position }} qr for
                                                                                            qty
                                                                                            {{ $key + 1 }}</p>
                                                                                        <img src="{{ asset('storage/' . $qrCode->qr_image_path) }}"
                                                                                            width="100" alt="QR Image">
                                                                                    </div>
                                                                                @endforeach
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                </td>

                                                        
                                                                <td>{{ $items->sum('qty') }}</td>

                                                                <td>${{ number_format($items->first()->price, 2) }}</td>
                                                                <td>${{ number_format($items->sum('total'), 2) }}</td>
                                                            </tr>
                                                        @endforeach

                                                     
                                                        <tr>
                                                            <td colspan="7" class="text-right"><strong>Order
                                                                    Total:</strong></td>
                                                            <td><strong>${{ number_format($order->orderItems->sum('total'), 2) }}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p>No order items found.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    @endpush
@endsection
