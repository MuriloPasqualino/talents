Olá,

@if ($company)
Foi criado um acesso ao portal Talents para a empresa {{ $company->name }}.
@else
Foi criado um acesso à equipa administrativa da plataforma Talents.
@endif

Seu usuário é o e-mail: {{ $user->email }}

Para definir sua senha, acesse:
{{ $resetPasswordUrl }}

Login: {{ url('/login') }}

Se você não reconhece este cadastro, ignore este e-mail.
