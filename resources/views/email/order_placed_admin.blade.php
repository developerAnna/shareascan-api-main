<!DOCTYPE html>
<html>

<head>
    <title>New Order Notification</title>
    <style type="text/css">
        .g-container {
            padding: 15px 30px;
        }

        .card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }

        .card-body {
            padding: 20px;
            font-family: 'Open Sans', sans-serif;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table td,
        .table th {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .table th {
            background-color: #111119;
            color: white;
        }

        .table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .footer {
            width: 100%;
            text-align: center;
            background-color: #111119;
            padding: 10px 0;
            color: #fff;
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="g-container">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <table style="max-width: 742px; margin: 0 auto; padding: 40px; font-family: 'Open Sans', sans-serif;"
                        border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
                        <tbody>
                            <tr>
                                <td>
                                    <table style="width: 100%; background-color: #ffffff; color: #000000;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center; padding: 20px 20px; background-color: #111119;"
                                                    colspan="3">
                                                    <a title="logo" href="#">
                                                        <img src="{{ url('admin/assets/img/logo/share_a_scan_logo.png') }}"
                                                            alt="" width="259" height="53" />
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 20px 20px 0px;">
                                                    <h3><span style="font-size: 14px;">Hello Admin,</span></h3>
                                                    <p
                                                        style="font-size: 16px; font-weight: 600; line-height: 26px; margin-top: 20px;">
                                                        <span style="font-size: 14px;">We have received a new
                                                            order:</span>
                                                    </p>
                                                    <p style="font-size: 16px; font-weight: 600; line-height: 26px;">
                                                        <span style="font-size: 14px;">Order ID:
                                                            {{ $mailData['order']->id }}</span><br />
                                                        <span style="font-size: 14px;">MerchMake Order ID:
                                                            {{ $mailData['order']->merchmake_order_id }}</span><br />
                                                        <span style="font-size: 14px;">Customer Name:
                                                            {{ $mailData['order']->shippingAddress->first_name . ' ' . $mailData['order']->shippingAddress->last_name }}</span><br />
                                                        <span style="font-size: 14px;">Customer Email:
                                                            {{ $mailData['order']->shippingAddress->email }}</span><br />
                                                    </p>

                                                    @if (!empty($mailData['order']))
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Product Title</th>
                                                                    <th>Variation</th>
                                                                    <th>Quantity</th>
                                                                    <th>Price</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                                @foreach ($mailData['order']->orderItems->groupBy('cart_id') as $cartId => $order_item)
                                                                    <tr>
                                                                        <td>{{ $order_item->first()->product_title }}
                                                                        </td>

                                                                        <td>
                                                                            {{ $order_item->first()->variation_color ? $order_item->first()->variation_color : '' }}
                                                                            {{ $order_item->first()->variation_color && $order_item->first()->variation_size ? ' | ' : '' }}
                                                                            {{ $order_item->first()->variation_size ? $order_item->first()->variation_size : '' }}
                                                                        </td>

                                                                        <td>{{ $order_item->sum('qty') }}</td>

                                                                        <td>$
                                                                            {{ number_format($order_item->first()->price, 2) }}
                                                                        </td>
                                                                        <td>$
                                                                            {{ number_format($order_item->sum('total'), 2) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="4"
                                                                        style="text-align: right; font-weight: bold;">
                                                                        Order Total</td>
                                                                    <td>
                                                                        $
                                                                        {{ number_format($mailData['order']->orderItems->sum('total'), 2) }}
                                                                    </td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 20px 20px 0px;">
                                                    <p style="font-size: 16px; font-weight: 600; line-height: 26px;">
                                                        <span style="font-size: 14px;">Please review the details and
                                                            take the necessary actions.</span>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 20px;" colspan="3">
                                                    <p
                                                        style="color: #000000; font-size: 16px; font-weight: 400; line-height: 24px; margin: 0; text-align: left;">
                                                        Regards,<br />
                                                        Share a Scan
                                                    </p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="footer">
                                        <p>&copy; Share a Scan | All Rights Reserved 2025.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
