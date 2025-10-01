@extends('admin.layouts.common')
@section('content')
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
                            <div class="card mb-4">
                                <div class="card-body">
                                    <!-- Two-column table for order/payment and shipping details -->
                                    <table class="w-100" style="border-collapse: collapse; width: 100%;">
                                        <tr>
                                            <!-- Left side: Order and Payment Details -->
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
                                    </table>


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
                                                                <!-- Product Title -->
                                                                <td>{{ $items->first()->product_title }}</td>

                                                                <!-- Merchmake Product ID -->
                                                                <td>{{ $items->first()->product_id }}</td>

                                                                <!-- Product Images -->
                                                                <td>
                                                                    <img src="{{ $image_url['image_url'] }}" width="100"
                                                                        alt="Product Image">
                                                                </td>

                                                                <!-- Variation -->
                                                                <td>{{ $items->first()->variation_color }},
                                                                    {{ $items->first()->variation_size }}</td>

                                                                <!-- QR Images -->
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

                                                                <!-- Quantity -->
                                                                <td>{{ $items->sum('qty') }}</td>

                                                                <!-- Price -->
                                                                <td>${{ number_format($items->first()->price, 2) }}</td>
                                                                <td>${{ number_format($items->sum('total'), 2) }}</td>
                                                            </tr>
                                                        @endforeach

                                                        <!-- Row for Order Total -->
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
