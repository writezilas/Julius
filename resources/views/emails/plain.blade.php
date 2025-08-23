@component('mail::message')

**{{ __('Hi') }} {{ $data['username']}},**

{!! $data['content'] !!}

{{ __('Thank You') }},<br>
{{ env('APP_NAME') }}
@endcomponent
