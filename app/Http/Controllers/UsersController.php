<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Middleware\AuthMiddleware;
use App\UseCases\UserUseCase;
use Core\Flash;

class UsersController extends Controller
{
    private UserUseCase $userUseCase;

    public function __construct(AuthMiddleware $authMiddleware, UserUseCase $userUseCase)
    {
        parent::__construct($authMiddleware);
        $this->userUseCase = $userUseCase;
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requireAdmin();
        $lista_tbl_usuarios = $this->userUseCase->listUsers();
        $this->renderWithLayout(
            'users/index.php',
            array_merge(
                compact('lista_tbl_usuarios'),
                $this->pageHeaderData(
                    'Gestión de Usuarios',
                    'fas fa-users-cog',
                    $this->moduleBreadcrumbs('Usuarios', 'usuarios', 'fas fa-users-cog')
                )
            )
        );
    }

    public function createForm(): void
    {
        $this->requireLogin();
        $this->requireAdmin();
        $formAction = 'usuarios-crear';
        $mensaje = '';
        $this->renderWithLayout(
            'users/create.php',
            array_merge(
                compact('formAction', 'mensaje'),
                $this->pageHeaderData(
                    'Nuevo Usuario',
                    'fas fa-user-plus',
                    $this->moduleBreadcrumbs('Usuarios', 'usuarios', 'fas fa-users-cog', 'Nuevo', 'fas fa-plus')
                )
            )
        );
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requireAdmin();
        if (!$this->hasValidCsrfToken($_POST)) {
            Flash::set('Solicitud inválida, recargue la página e intente nuevamente.', 'error');
            $this->redirect('usuarios-crear');
        }

        $req    = StoreUserRequest::fromArray($_POST);
        $errors = $req->validate();
        if ($errors !== []) {
            Flash::set((string)reset($errors), 'error');
            $this->redirect('usuarios-crear');
        }

        $result = $this->userUseCase->createUser($req, $_SESSION['user_id'] ?? null);
        if ($result->success) {
            Flash::set($result->message ?? 'Usuario creado exitosamente.');
            $this->redirect('usuarios');
        }

        $formAction = 'usuarios-crear';
        $mensaje    = $result->message ?? 'No se pudo crear el usuario.';
        $this->renderWithLayout(
            'users/create.php',
            array_merge(
                compact('formAction', 'mensaje'),
                $this->pageHeaderData(
                    'Nuevo Usuario',
                    'fas fa-user-plus',
                    $this->moduleBreadcrumbs('Usuarios', 'usuarios', 'fas fa-users-cog', 'Nuevo', 'fas fa-plus')
                )
            )
        );
    }

    public function editForm(): void
    {
        $this->requireLogin();
        $this->requireAdmin();
        $txtID       = (int)($_GET['txtID'] ?? 0);
        $usuarioData = $this->userUseCase->getUser($txtID);
        if ($usuarioData === null) {
            Flash::set('No se encontró el usuario a editar.', 'error');
            $this->redirect('usuarios');
        }

        $formAction = 'usuarios-editar';
        $mensaje    = '';
        $usuario    = (string)($usuarioData['Nombreusuario'] ?? '');
        $correo     = (string)($usuarioData['Correo'] ?? '');
        $this->renderWithLayout(
            'users/edit.php',
            array_merge(
                compact('formAction', 'mensaje', 'txtID', 'usuario', 'correo'),
                $this->pageHeaderData(
                    'Editar Usuario',
                    'fas fa-user-edit',
                    $this->moduleBreadcrumbs('Usuarios', 'usuarios', 'fas fa-users-cog', 'Editar', 'fas fa-pen')
                )
            )
        );
    }

    public function edit(): void
    {
        $this->requireLogin();
        $this->requireAdmin();
        if (!$this->hasValidCsrfToken($_POST)) {
            Flash::set('Solicitud inválida, recargue la página e intente nuevamente.', 'error');
            $this->redirect('usuarios');
        }

        $req    = UpdateUserRequest::fromArray($_POST);
        $errors = $req->validate();
        if ($errors !== []) {
            Flash::set((string)reset($errors), 'error');
            $this->redirect('usuarios');
        }

        $result = $this->userUseCase->updateUser($req, $_SESSION['user_id'] ?? null);
        if ($result->success) {
            Flash::set($result->message ?? 'Usuario actualizado exitosamente.');
            $this->redirect('usuarios');
        }

        $txtID      = $req->id;
        $formAction = 'usuarios-editar';
        $mensaje    = $result->message ?? 'No se pudo actualizar el usuario.';
        $usuario    = $req->usuario;
        $correo     = $req->correo;
        $this->renderWithLayout(
            'users/edit.php',
            array_merge(
                compact('formAction', 'mensaje', 'txtID', 'usuario', 'correo'),
                $this->pageHeaderData(
                    'Editar Usuario',
                    'fas fa-user-edit',
                    $this->moduleBreadcrumbs('Usuarios', 'usuarios', 'fas fa-users-cog', 'Editar', 'fas fa-pen')
                )
            )
        );
    }

    public function delete(): void
    {
        $this->requireLogin();
        $this->requireAdmin();
        $isAjax = $this->isAjaxRequest();

        if (!$this->hasValidCsrfToken($_POST)) {
            $msg = 'Solicitud inválida, recargue la página e intente nuevamente.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Flash::set($msg, 'error');
            $this->redirect('usuarios');
        }

        $txtID = (int)($_POST['txtID'] ?? 0);

        if ($txtID > 0) {
            $deleted = $this->userUseCase->deleteUser($txtID, $_SESSION['user_id'] ?? null);
            $success = $deleted;
            $message = $deleted ? 'Usuario eliminado exitosamente.' : 'No se pudo eliminar el usuario.';
        } else {
            $success = false;
            $message = 'El ID del usuario no es válido.';
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $message]);
            exit;
        }

        Flash::set($message, $success ? 'success' : 'error');
        $this->redirect('usuarios');
    }
}
