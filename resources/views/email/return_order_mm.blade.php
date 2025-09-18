<!DOCTYPE html>
<html>

<head>
    <title></title>
    <style type="text/css">
        .g-container {
            padding: 15px 30px;
        }
    </style>
</head>

<body>
    <div class="g-container">
        <div class="container">
            <div class="card" style="background-color: #f8f9fa; border: 1px solid #ddd;">
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
                                                    <h3><span style="font-size: 14px;">Hello MerchMake Support,</span>
                                                    </h3>
                                                    <p
                                                        style="font-size: 16px; font-weight: 600; line-height: 26px; margin-top: 20px; margin-bottom: 0;">
                                                        <span style="font-size: 14px;">We would like to raise a support
                                                            ticket regarding a return order request. Please see the
                                                            details below:</span>
                                                    </p>
                                                    <p
                                                        style="font-size: 16px; font-weight: 600; line-height: 26px; margin-top: 20px; margin-bottom: 0;">
                                                        <span style="font-size: 14px;">Order ID:
                                                            {{ $mailData['return_order']['order_id'] }}</span><br />
                                                        <span style="font-size: 14px;">MerchMake Order ID:
                                                            {{ $mailData['return_order']->order->merchmake_order_id }}</span><br />
                                                        <span style="font-size: 14px;">Customer Name:
                                                            {{ $mailData['return_order']->order->user->name . ' ' . $mailData['return_order']->order->user->last_name }}</span><br />
                                                        <span style="font-size: 14px;">Customer Email:
                                                            {{ $mailData['return_order']->order->user->email }}</span><br />
                                                        <span style="font-size: 14px;">Return Reason:
                                                            {{ $mailData['return_order']->reason }}</span><br />
                                                        <span style="font-size: 14px;">Description:
                                                            {{ $mailData['return_order']->description }}</span><br />
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
                                    <table
                                        style="width: 100%; padding: 20px 0px; text-align: center; background-color: #111119; height: 22px;">
                                        <tbody>
                                            <tr style="height: 22px;">
                                                <td style="height: 22px;">
                                                    <p
                                                        style="color: #ffffff; font-size: 16px; line-height: 22px; text-align: center; margin: 0px;">
                                                        &copy; Share a Scan | All Rights Reserved 2025.</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
