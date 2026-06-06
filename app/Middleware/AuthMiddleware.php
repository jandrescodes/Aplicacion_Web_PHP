<?php

namespace App\Middleware;

use App\UseCases\AuthUseCase;
use Core\Security;

class AuthMiddleware
{
    private AuthUseCase $authUseCase;

    public function __construct(AuthUseCase $authUseCase)
    {
        $this->authUseCase = $authUseCase;
    }

    public function requireLogin(string $loginUrl): void
    {
        Security::startSession();

        if (isset($_SESSION['logueado'])) {
            return;
        }

        if ($this->authUseCase->handleRememberLogin()) {
            return;
        }

        header('Location:' . $loginUrl);
        exit();
    }

    public function currentUser()
    {
        Security::startSession();

        return isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '';
    }

    public function isAdmin(): bool
    {
        Security::startSession();

        return !empty($_SESSION['is_admin']);
    }
}
