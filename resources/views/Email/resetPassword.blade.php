@component('mail::message')
<h1>Hola!</h1>
<h1>{{ $perfil }}</h1>

<p>Recibió este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para su cuenta.</p>

@component('mail::button', ['url' => $url])
Restablecer la contraseña
@endcomponent

<p>Este enlace de restablecimiento de contraseña caducará en 60 minutos.</p>
<p>Si no solicitó un restablecimiento de contraseña, por favor ignore este correo electrónico y perdone las molestias.</p>

Gracias,<br>
{{ config('app.name') }}
<br>
@endcomponent