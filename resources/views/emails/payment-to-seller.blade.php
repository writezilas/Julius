@component('mail::message')

**{{ __('Hi') }} {{$payment->receiver->username}},**

{{ __('You have received a payment from') }} {{$payment->sender->username}}

@component('mail::table')
    | <!-- -->    | <!-- -->    |
    |-------------|-------------|
    | {{ __('Sender Name') }}: | {{ json_decode($payment->sender->business_profile)->mpesa_name }} |
    | {{ __('Sender M-PESA no') }}: | {{ $payment->number }} |
    | {{ __('Amount Sent') }}:  | {{ formatPrice($payment->amount) }} |
    | {{ __('Transaction Id') }}: | {{ $payment->txs_id }} |
    | {{ __('Note From Buyer') }}: | {{ $payment->note_by_sender }} |

@endcomponent

@component('mail::button', ['url' => route('sold-share.view',$payment->paired->paired_user_share_id)])
    {{ __('View payment') }}
@endcomponent

{{ __('Thank You') }},<br>
{{ env('APP_NAME') }}
@endcomponent
