@component('mail::message')

**{{ __('Hi Support') }} ,**



@component('mail::table')
    | <!-- -->    | <!-- -->    |
    |-------------|-------------|
    | {{ __('Name') }}: | {{ $support->fisrt_name }} {{ $support->last_name }} |
    | {{ __('Email') }}: | {{ $support->email }} |
    | {{ __('Username') }}: | {{ $support->username }} |
    | {{ __('Number') }}: | {{ $support->number }} |
    | {{ __('Message') }}: | {{ $support->message }} |
@endcomponent

@component('mail::button', ['url' => route('admin.support')])
    {{ __('View Ticket') }}
@endcomponent

{{ __('Thank You') }},<br>
{{ env('APP_NAME') }}
@endcomponent
