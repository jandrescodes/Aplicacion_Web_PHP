<?php

namespace App\Http\Controllers;

use App\Http\Requests\Positions\StorePositionRequest;
use App\Http\Requests\Positions\UpdatePositionRequest;
use App\Middleware\AuthMiddleware;
use App\UseCases\PositionUseCase;
use Core\Flash;

class PositionsController extends Controller
{
    private PositionUseCase $positionUseCase;

    public function __construct(AuthMiddleware $authMiddleware, PositionUseCase $positionUseCase)
    {
        parent::__construct($authMiddleware);
        $this->positionUseCase = $positionUseCase;
    }

    public function index(): void
    {
        $this->requireLogin();
        $lista_tbl_puestos = $this->positionUseCase->listPositions();
        $this->renderWithLayout(
            'positions/index.php',
            array_merge(
                compact('lista_tbl_puestos'),
                $this->pageHeaderData(
                    'Gestión de Puestos',
                    'fas fa-briefcase',
                    $this->moduleBreadcrumbs('Puestos', 'puestos', 'fas fa-briefcase')
                )
            )
        );
    }

    public function createForm(): void
    {
        $this->requireLogin();
        $formAction = 'puestos-crear';
        $mensaje = '';
        $this->renderWithLayout(
            'positions/create.php',
            array_merge(
                compact('formAction', 'mensaje'),
                $this->pageHeaderData(
                    'Nuevo Puesto',
                    'fas fa-plus-circle',
                    $this->moduleBreadcrumbs('Puestos', 'puestos', 'fas fa-briefcase', 'Nuevo', 'fas fa-plus')
                )
            )
        );
    }

    public function create(): void
    {
        $this->requireLogin();
        if (!$this->hasValidCsrfToken($_POST)) {
            Flash::set('Solicitud inválida, recargue la página e intente nuevamente.', 'error');
            $this->redirect('puestos-crear');
        }

        $req    = StorePositionRequest::fromArray($_POST);
        $errors = $req->validate();
        if ($errors !== []) {
            Flash::set((string)reset($errors), 'error');
            $this->redirect('puestos-crear');
        }

        $result = $this->positionUseCase->createPosition($req, $_SESSION['user_id'] ?? null);
        if ($result->success) {
            Flash::set($result->message ?? 'Puesto creado exitosamente.');
            $this->redirect('puestos');
        }

        $formAction = 'puestos-crear';
        $mensaje    = $result->message ?? 'No se pudo crear el puesto.';
        $this->renderWithLayout(
            'positions/create.php',
            array_merge(
                compact('formAction', 'mensaje'),
                $this->pageHeaderData(
                    'Nuevo Puesto',
                    'fas fa-plus-circle',
                    $this->moduleBreadcrumbs('Puestos', 'puestos', 'fas fa-briefcase', 'Nuevo', 'fas fa-plus')
                )
            )
        );
    }

    public function editForm(): void
    {
        $this->requireLogin();
        $txtID  = (int)($_GET['txtID'] ?? 0);
        $puesto = $this->positionUseCase->getPosition($txtID);
        if ($puesto === null) {
            Flash::set('No se encontró el puesto a editar.', 'error');
            $this->redirect('puestos');
        }

        $formAction      = 'puestos-editar';
        $mensaje         = '';
        $nombredelpuesto = (string)($puesto['Nombredelpuesto'] ?? '');
        $this->renderWithLayout(
            'positions/edit.php',
            array_merge(
                compact('formAction', 'mensaje', 'txtID', 'nombredelpuesto'),
                $this->pageHeaderData(
                    'Editar Puesto',
                    'fas fa-edit',
                    $this->moduleBreadcrumbs('Puestos', 'puestos', 'fas fa-briefcase', 'Editar', 'fas fa-pen')
                )
            )
        );
    }

    public function edit(): void
    {
        $this->requireLogin();
        if (!$this->hasValidCsrfToken($_POST)) {
            Flash::set('Solicitud inválida, recargue la página e intente nuevamente.', 'error');
            $this->redirect('puestos');
        }

        $req    = UpdatePositionRequest::fromArray($_POST);
        $errors = $req->validate();
        if ($errors !== []) {
            Flash::set((string)reset($errors), 'error');
            $this->redirect('puestos');
        }

        $result = $this->positionUseCase->updatePosition($req, $_SESSION['user_id'] ?? null);
        if ($result->success) {
            Flash::set($result->message ?? 'Puesto actualizado exitosamente.');
            $this->redirect('puestos');
        }

        $txtID           = $req->id;
        $formAction      = 'puestos-editar';
        $mensaje         = $result->message ?? 'No se pudo actualizar el puesto.';
        $nombredelpuesto = $req->nombre;
        $this->renderWithLayout(
            'positions/edit.php',
            array_merge(
                compact('formAction', 'mensaje', 'txtID', 'nombredelpuesto'),
                $this->pageHeaderData(
                    'Editar Puesto',
                    'fas fa-edit',
                    $this->moduleBreadcrumbs('Puestos', 'puestos', 'fas fa-briefcase', 'Editar', 'fas fa-pen')
                )
            )
        );
    }

    public function delete(): void
    {
        $this->requireLogin();
        $isAjax = $this->isAjaxRequest();

        if (!$this->hasValidCsrfToken($_POST)) {
            $msg = 'Solicitud inválida, recargue la página e intente nuevamente.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Flash::set($msg, 'error');
            $this->redirect('puestos');
        }

        $txtID = (int)($_POST['txtID'] ?? 0);

        if ($txtID > 0) {
            $deleted = $this->positionUseCase->deletePosition($txtID, $_SESSION['user_id'] ?? null);
            $success = $deleted;
            $message = $deleted ? 'Puesto eliminado exitosamente.' : 'No se pudo eliminar el puesto.';
        } else {
            $success = false;
            $message = 'El ID del puesto no es válido.';
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $message]);
            exit;
        }

        Flash::set($message, $success ? 'success' : 'error');
        $this->redirect('puestos');
    }
}
