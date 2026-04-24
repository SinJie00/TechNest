<x-mail::message>
# Order Confirmation: #{{ $order->id }}

Hi {{ $order->contact_name }},

Thank you for your order! Your payment has been successfully processed and your order is confirmed.

### Order Summary

<x-mail::table>
| Item       | Quantity         | Price  |
| :--------- | :------------- | --------:|
@foreach($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | RM {{ number_format($item->price, 2) }} |
@endforeach
| | **Shipping Fee** | **RM {{ number_format($order->shipping_fee, 2) }}** |
| | **Total** | **RM {{ number_format($order->total_price, 2) }}** |
</x-mail::table>

We will notify you once your order is shipped.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
