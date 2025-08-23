@component('mail::message')

**{{ __('Hi') }} {{ $payment->sender->username}},**

{{$payment->receiver->username}} {{ __('  has confirmed your payment of ') }} {{ formatPrice($payment->amount) }} {{ __(' The payment id is ') }} {{ $payment->txs_id }}

@component('mail::table')
    | <!-- -->    | <!-- -->    |
    |-------------|-------------|
    | {{ __('Note from the Seller') }}: | {{ $payment->note_by_receiver }} |

@endcomponent

@component('mail::button', ['url' => route('bought-share.view',$payment->user_share_id)])
    {{ __('View Order') }}
@endcomponent

{{ __('Thank You') }},<br>
{{ env('APP_NAME') }}
@endcomponent
