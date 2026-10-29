<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>H&K Consumer Goods Store Order Update</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.6;">
    <h2>H&K Consumer Goods Store</h2>

    <p>Hello {{ $order->recipient_name }},</p>

    @switch($event)
        @case('placed')
            <p>Thank you for placing your order. We have received it and will review it shortly.</p>
            @break

        @case('accepted')
            <p>Good news! Your order has been accepted and the stock has been allocated.</p>
            @break

        @case('rider_assigned')
            <p>A rider has been assigned to your order.</p>

            @if(filled($riderName))
                <p><strong>Rider:</strong> {{ $riderName }}</p>
            @endif
            @break

        @case('completed')
            <p>Your order has been marked as completed. Thank you for choosing H&K Consumer Goods Store!</p>
            @break

        @case('cancelled')
            <p>Your order has been cancelled. Please contact our store if you have questions.</p>
            @break

        @default
            <p>There is an update regarding your order.</p>
    @endswitch

    <hr>

    <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
    <p><strong>Status:</strong> {{ $order->display_status }}</p>
    <p><strong>Total:</strong> ₱{{ number_format((float) $order->total, 2) }}</p>

    <p>Please sign in to your H&K Consumer Goods Store account to view your order details.</p>

    <p>Thank you,<br>H&K Consumer Goods Store</p>
</body>
</html>