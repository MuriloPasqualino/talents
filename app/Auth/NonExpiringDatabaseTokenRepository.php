<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;

/**
 * Tokens de convite (definir senha inicial) não expiram até serem usados.
 * O link de "esqueci minha senha" continua usando o broker padrão com validade curta.
 */
class NonExpiringDatabaseTokenRepository extends DatabaseTokenRepository
{
    protected function tokenExpired($createdAt): bool
    {
        return false;
    }
}
