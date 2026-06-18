<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use App\Middleware\AuthMiddleware;
use App\UseCases\EmployeeUseCase;
use Core\Flash;
use Core\View;
use Dompdf\Dompdf;
use Dompdf\Options;

class EmployeesController extends Controller
{
    private EmployeeUseCase $employeeUseCase;

    public function __construct(AuthMiddleware $authMiddleware, EmployeeUseCase $employeeUseCase)
    {
        parent::__construct($authMiddleware);
        $this->employeeUseCase = $employeeUseCase;
    }

    public function index(): void
    {
        $this->requireLogin();
        $lista_tbl_empleados = $this->employeeUseCase->listEmployees();
        $this->renderWithLayout(
            'employees/index.php',
            array_merge(
                compact('lista_tbl_empleados'),
                $this->pageHeaderData(
                    'Gestión de Empleados',
                    'fas fa-users',
                    $this->moduleBreadcrumbs('Empleados', 'empleados', 'fas fa-users')
                )
            )
        );
    }

    public function createForm(): void
    {
        $this->requireLogin();
        $lista_tbl_puestos = $this->employeeUseCase->listPositions();
        $formAction = 'empleados-crear';
        $mensaje = '';
        $this->renderWithLayout(
            'employees/create.php',
            array_merge(
                compact('lista_tbl_puestos', 'formAction', 'mensaje'),
                $this->pageHeaderData(
                    'Nuevo Empleado',
                    'fas fa-user-plus',
                    $this->moduleBreadcrumbs('Empleados', 'empleados', 'fas fa-users', 'Nuevo', 'fas fa-plus')
                )
            )
        );
    }

    public function create(): void
    {
        $this->requireLogin();
        if (!$this->hasValidCsrfToken($_POST)) {
            Flash::set('Solicitud inválida, recargue la página e intente nuevamente.', 'error');
            $this->redirect('empleados-crear');
        }

        $req    = StoreEmployeeRequest::fromArray($_POST);
        $errors = $req->validate();
        if ($errors !== []) {
            Flash::set((string)reset($errors), 'error');
            $this->redirect('empleados-crear');
        }

        $result = $this->employeeUseCase->createEmployee($req, $_FILES, $this->uploadsDirectory, $_SESSION['user_id'] ?? null);
        if ($result->success) {
            Flash::set($result->message ?? 'Empleado creado exitosamente.');
            $this->redirect('empleados');
        }

        $lista_tbl_puestos = $this->employeeUseCase->listPositions();
        $formAction = 'empleados-crear';
        $mensaje    = $result->message ?? 'No se pudo crear el empleado.';
        $this->renderWithLayout(
            'employees/create.php',
            array_merge(
                compact('lista_tbl_puestos', 'formAction', 'mensaje'),
                $this->pageHeaderData(
                    'Nuevo Empleado',
                    'fas fa-user-plus',
                    $this->moduleBreadcrumbs('Empleados', 'empleados', 'fas fa-users', 'Nuevo', 'fas fa-plus')
                )
            )
        );
    }

    public function editForm(): void
    {
        $this->requireLogin();
        $txtID    = (int)($_GET['txtID'] ?? 0);
        $empleado = $this->employeeUseCase->getEmployee($txtID);
        if ($empleado === null) {
            Flash::set('No se encontró el empleado a editar.', 'error');
            $this->redirect('empleados');
        }

        $lista_tbl_puestos = $this->employeeUseCase->listPositions();
        $formAction        = 'empleados-editar';
        $mensaje           = '';
        $primernombre      = (string)($empleado['Primernombre'] ?? '');
        $segundonombre     = (string)($empleado['Segundonombre'] ?? '');
        $primerapellido    = (string)($empleado['Primerapellido'] ?? '');
        $segundoapellido   = (string)($empleado['Segundoapellido'] ?? '');
        $foto              = (string)($empleado['Foto'] ?? '');
        $cv                = (string)($empleado['CV'] ?? '');
        $idpuesto          = (string)($empleado['Idpuesto'] ?? '');
        $fechadeingreso    = (string)($empleado['Fecha'] ?? '');

        $this->renderWithLayout(
            'employees/edit.php',
            array_merge(
                compact(
                    'lista_tbl_puestos', 'formAction', 'mensaje', 'txtID',
                    'primernombre', 'segundonombre', 'primerapellido', 'segundoapellido',
                    'foto', 'cv', 'idpuesto', 'fechadeingreso'
                ),
                $this->pageHeaderData(
                    'Editar Empleado',
                    'fas fa-user-edit',
                    $this->moduleBreadcrumbs('Empleados', 'empleados', 'fas fa-users', 'Editar', 'fas fa-pen')
                )
            )
        );
    }

    public function edit(): void
    {
        $this->requireLogin();
        if (!$this->hasValidCsrfToken($_POST)) {
            Flash::set('Solicitud inválida, recargue la página e intente nuevamente.', 'error');
            $this->redirect('empleados');
        }

        $req    = UpdateEmployeeRequest::fromArray($_POST);
        $errors = $req->validate();
        if ($errors !== []) {
            Flash::set((string)reset($errors), 'error');
            $this->redirect('empleados');
        }

        $result = $this->employeeUseCase->updateEmployee($req, $_FILES, $this->uploadsDirectory, $_SESSION['user_id'] ?? null);
        if ($result->success) {
            Flash::set($result->message ?? 'Empleado actualizado exitosamente.');
            $this->redirect('empleados');
        }

        $empleado = $this->employeeUseCase->getEmployee($req->id);
        if ($empleado === null) {
            Flash::set('No se encontró el empleado a editar.', 'error');
            $this->redirect('empleados');
        }

        $lista_tbl_puestos = $this->employeeUseCase->listPositions();
        $txtID             = $req->id;
        $formAction        = 'empleados-editar';
        $mensaje           = $result->message ?? 'No se pudo actualizar el empleado.';
        $primernombre      = $req->primerNombre;
        $segundonombre     = $req->segundoNombre ?? '';
        $primerapellido    = $req->primerApellido;
        $segundoapellido   = $req->segundoApellido;
        $foto              = (string)($empleado['Foto'] ?? '');
        $cv                = (string)($empleado['CV'] ?? '');
        $idpuesto          = (string)$req->idPuesto;
        $fechadeingreso    = $req->fechaIngreso;

        $this->renderWithLayout(
            'employees/edit.php',
            array_merge(
                compact(
                    'lista_tbl_puestos', 'formAction', 'mensaje', 'txtID',
                    'primernombre', 'segundonombre', 'primerapellido', 'segundoapellido',
                    'foto', 'cv', 'idpuesto', 'fechadeingreso'
                ),
                $this->pageHeaderData(
                    'Editar Empleado',
                    'fas fa-user-edit',
                    $this->moduleBreadcrumbs('Empleados', 'empleados', 'fas fa-users', 'Editar', 'fas fa-pen')
                )
            )
        );
    }

    public function recommendation(): void
    {
        $this->requireLogin();
        $txtID    = (int)($_GET['txtID'] ?? 0);
        $empleado = $this->employeeUseCase->getEmployeeWithPosition($txtID);
        if ($empleado === null) {
            Flash::set('No se encontró el empleado para la carta de recomendación.', 'error');
            $this->redirect('empleados');
        }

        $nombreCompleto = trim(preg_replace('/\s+/', ' ', implode(' ', [
            (string)($empleado['Primernombre'] ?? ''),
            (string)($empleado['Segundonombre'] ?? ''),
            (string)($empleado['Primerapellido'] ?? ''),
            (string)($empleado['Segundoapellido'] ?? ''),
        ])));
        $puesto       = (string)($empleado['puesto'] ?? '');
        $fechaIngreso = \DateTime::createFromFormat('Y-m-d', (string)($empleado['Fecha'] ?? ''));
        $fechaIngreso = $fechaIngreso ?: new \DateTime();
        $diferencia   = $fechaIngreso->diff(new \DateTime());
        $fechaActual  = (new \DateTime())->format('d/m/Y');

        $html = View::capture('employees/recommendation_letter.php', compact(
            'nombreCompleto', 'puesto', 'fechaIngreso', 'diferencia', 'fechaActual'
        ));

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'carta_recomendacion_' . preg_replace('/\s+/', '_', strtolower($nombreCompleto)) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
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
            $this->redirect('empleados');
        }

        $txtID = (int)($_POST['txtID'] ?? 0);

        if ($txtID > 0) {
            $deleted = $this->employeeUseCase->deleteEmployee($txtID, $this->uploadsDirectory, $_SESSION['user_id'] ?? null);
            $success = $deleted;
            $message = $deleted ? 'Empleado eliminado exitosamente.' : 'No se pudo eliminar el empleado.';
        } else {
            $success = false;
            $message = 'El ID del empleado no es válido.';
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $message]);
            exit;
        }

        Flash::set($message, $success ? 'success' : 'error');
        $this->redirect('empleados');
    }
}
