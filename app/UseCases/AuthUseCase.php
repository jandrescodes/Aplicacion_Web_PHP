<?php

namespace App\UseCases;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\UseCases\DTOs\OperationResult;
use Core\Security;

class AuthUseCase
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function handleLogin(LoginRequest $request): OperationResult
    {
        $user = $this->authService->authenticate($request->usuario, $request->password);
        if ($user === null) {
            return OperationResult::fail('El usuario o la contraseña son incorrectos');
        }

        Security::startSession();
        session_regenerate_id(true);

        $_SESSION['usuario']  = $user->usuario;
        $_SESSION['logueado'] = true;
        $_SESSION['user_id']  = $user->id;
        $_SESSION['is_admin'] = $user->isAdmin;

        if ($request->remember && (($_ENV['REMEMBER_ME_ENABLED'] ?? 'true') === 'true')) {
            $plainToken = $this->authService->issueRememberToken($user->id);
            Security::setRememberCookie($user->id . ':' . $plainToken);
        }

        return OperationResult::ok();
    }

    public function handleRememberLogin(): bool
    {
        if (($_ENV['REMEMBER_ME_ENABLED'] ?? 'true') !== 'true') {
            return false;
        }

        $cookie = Security::getRememberCookie();
        if ($cookie === null) {
            return false;
        }

        $user = $this->authService->validateRememberToken($cookie);
        if ($user === null) {
            Security::clearRememberCookie();
            return false;
        }

        Security::startSession();
        session_regenerate_id(true);

        $_SESSION['usuario']  = $user->usuario;
        $_SESSION['logueado'] = true;
        $_SESSION['user_id']  = $user->id;
        $_SESSION['is_admin'] = $user->isAdmin;

        $plainToken = $this->authService->issueRememberToken($user->id);
        Security::setRememberCookie($user->id . ':' . $plainToken);

        return true;
    }

    public function handleLogout(): void
    {
        Security::startSession();

        if (isset($_SESSION['user_id'])) {
            $this->authService->revokeRememberToken((int)$_SESSION['user_id']);
        }

        session_unset();
        session_destroy();
        Security::clearRememberCookie();
    }
}
