<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Middleware\AuthMiddleware;
use App\UseCases\ProfileUseCase;
use Core\Flash;
use Core\Security;

class ProfileController extends Controller
{
    public function __construct(
        AuthMiddleware $authMiddleware,
        private ProfileUseCase $profileUseCase
    ) {
        parent::__construct($authMiddleware);
    }

    public function show(): void
    {
        $this->requireLogin();

        Security::startSession();
        $userId  = (int)($_SESSION['user_id'] ?? 0);
        $profile = $this->profileUseCase->getProfile($userId);

        if ($profile === null) {
            Flash::set('No se pudo cargar el perfil.', 'error');
            $this->redirect('');
        }

        $this->renderWithLayout('profile/show.php', array_merge(
            $profile,
            $this->pageHeaderData('Mi Perfil', 'fas fa-user-circle', $this->moduleBreadcrumbs(
                'Mi Perfil', 'perfil', 'fas fa-user-circle'
            )),
            ['isAdmin' => (bool)($_SESSION['is_admin'] ?? false)]
        ));
    }

    public function updateData(): void
    {
        $this->requireLogin();

        if (!$this->hasValidCsrfToken($_POST)) {
            Flash::set('Token de seguridad inválido.', 'error');
            $this->redirect('perfil');
        }

        Security::startSession();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $req    = UpdateProfileRequest::forUser($userId, $_POST);
        $errors = $req->validate();

        if (!empty($errors)) {
            Flash::set(array_values($errors)[0], 'error');
            $this->redirect('perfil');
        }

        $result = $this->profileUseCase->updateData($req);

        if ($result->success) {
            Flash::set($result->message ?? 'Datos actualizados exitosamente.');
        } else {
            Flash::set($result->message ?? 'No se pudo actualizar el perfil.', 'error');
        }

        $this->redirect('perfil');
    }

    public function changePassword(): void
    {
        $this->requireLogin();

        if (!$this->hasValidCsrfToken($_POST)) {
            Flash::set('Token de seguridad inválido.', 'error');
            $this->redirect('perfil');
        }

        Security::startSession();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $req    = ChangePasswordRequest::forUser($userId, $_POST);
        $errors = $req->validate();

        if (!empty($errors)) {
            Flash::set(array_values($errors)[0], 'error');
            $this->redirect('perfil');
        }

        $result = $this->profileUseCase->changePassword($req);

        if ($result->success) {
            Flash::set($result->message ?? 'Contraseña actualizada exitosamente.');
        } else {
            Flash::set($result->message ?? 'No se pudo cambiar la contraseña.', 'error');
        }

        $this->redirect('perfil');
    }
}
