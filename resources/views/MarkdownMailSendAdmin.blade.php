@component('mail::message')
    Hello, {{ $nama }}

    Kritik dan saranmu telah kami terima.

    Terima kasih, {{ config('app.name') }}
@endcomponent
