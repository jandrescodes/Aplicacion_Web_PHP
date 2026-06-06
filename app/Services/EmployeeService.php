<?php

namespace App\Services;

use App\Domain\Contracts\EmployeeRepositoryInterface;
use App\Domain\Models\Employee;
use App\Domain\Models\Position;
use App\Infrastructure\EmployeeFileStorage;
use PDOException;

class EmployeeService
{
    private EmployeeRepositoryInterface $employeeRepository;
    private EmployeeFileStorage $fileStorage;

    public function __construct(EmployeeRepositoryInterface $employeeRepository, EmployeeFileStorage $fileStorage)
    {
        $this->employeeRepository = $employeeRepository;
        $this->fileStorage = $fileStorage;
    }

    /** @return array<Employee> */
    public function listEmployees(): array
    {
        return $this->employeeRepository->listAllWithPosition();
    }

    /** @return array<Position> */
    public function listPositions(): array
    {
        return $this->employeeRepository->listPositions();
    }

    public function getEmployee(int $id): ?Employee
    {
        return $this->employeeRepository->findById($id);
    }

    public function getEmployeeWithPosition(int $id): ?Employee
    {
        return $this->employeeRepository->findByIdWithPosition($id);
    }

    public function createEmployee(array $data, array $files, string $baseDirectory)
    {
        $validationError = $this->validateEmployeeData($data);
        if ($validationError !== null) {
            return ['success' => false, 'message' => $validationError];
        }

        $photoError = null;
        $cvError = null;
        $photoName = $this->fileStorage->storeUploadedFile(isset($files['foto']) ? $files['foto'] : [], $baseDirectory, $photoError, ['jpg', 'jpeg', 'png', 'webp']);
        $cvName = $this->fileStorage->storeUploadedFile(isset($files['CV']) ? $files['CV'] : [], $baseDirectory, $cvError, ['pdf']);
        if ($photoError !== null || $cvError !== null) {
            if ($photoName !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $photoName);
            }
            if ($cvName !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $cvName);
            }
            return ['success' => false, 'message' => $this->mergeUploadErrors($photoError, $cvError)];
        }

        try {
            $created = $this->employeeRepository->create([
                'Primernombre' => trim((string)($data['primernombre'] ?? '')),
                'Segundonombre' => $this->nullIfEmpty($data['segundonombre'] ?? null),
                'Primerapellido' => trim((string)($data['primerapellido'] ?? '')),
                'Segundoapellido' => trim((string)($data['segundoapellido'] ?? '')),
                'Foto' => $this->nullIfEmpty($photoName) ?? 'user-default.jpg',
                'CV' => $this->nullIfEmpty($cvName) ?? 'cv_default.pdf',
                'Idpuesto' => (int)($data['idpuesto'] ?? 0),
                'Fecha' => (string)($data['fechadeingreso'] ?? '')
            ]);
        } catch (PDOException $exception) {
            if ($photoName !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $photoName);
            }
            if ($cvName !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $cvName);
            }
            return ['success' => false, 'message' => 'No se pudo crear el empleado.'];
        }

        if (!$created) {
            if ($photoName !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $photoName);
            }
            if ($cvName !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $cvName);
            }
            return ['success' => false, 'message' => 'No se pudo crear el empleado.'];
        }

        return ['success' => true, 'message' => 'Empleado creado exitosamente.'];
    }

    public function updateEmployee(int $id, array $data, array $files, string $baseDirectory)
    {
        $validationError = $this->validateEmployeeData($data);
        if ($validationError !== null) {
            return ['success' => false, 'message' => $validationError];
        }

        $employeeId = $id;
        if ($employeeId <= 0) {
            return ['success' => false, 'message' => 'El ID del empleado no es válido.'];
        }

        $existingEmployee = $this->employeeRepository->findById($employeeId);
        if ($existingEmployee === null) {
            return ['success' => false, 'message' => 'No se encontró el empleado a editar.'];
        }

        $currentPhoto = $existingEmployee->foto ?? 'user-default.jpg';
        $currentCv    = $existingEmployee->cv ?? 'cv_default.pdf';

        $photoError = null;
        $cvError = null;
        $newPhoto = $this->fileStorage->storeUploadedFile(isset($files['foto']) ? $files['foto'] : [], $baseDirectory, $photoError, ['jpg', 'jpeg', 'png', 'webp']);
        $newCv = $this->fileStorage->storeUploadedFile(isset($files['CV']) ? $files['CV'] : [], $baseDirectory, $cvError, ['pdf']);
        if ($photoError !== null || $cvError !== null) {
            return ['success' => false, 'message' => $this->mergeUploadErrors($photoError, $cvError)];
        }

        $photoToPersist = $newPhoto !== '' ? $newPhoto : $currentPhoto;
        $cvToPersist = $newCv !== '' ? $newCv : $currentCv;

        try {
            $updated = $this->employeeRepository->update($employeeId, [
                'Primernombre' => trim((string)($data['primernombre'] ?? '')),
                'Segundonombre' => $this->nullIfEmpty($data['segundonombre'] ?? null),
                'Primerapellido' => trim((string)($data['primerapellido'] ?? '')),
                'Segundoapellido' => trim((string)($data['segundoapellido'] ?? '')),
                'Foto' => $this->nullIfEmpty($photoToPersist),
                'CV' => $this->nullIfEmpty($cvToPersist),
                'Idpuesto' => (int)($data['idpuesto'] ?? 0),
                'Fecha' => (string)($data['fechadeingreso'] ?? '')
            ]);
        } catch (PDOException $exception) {
            if ($newPhoto !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $newPhoto);
            }
            if ($newCv !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $newCv);
            }
            return ['success' => false, 'message' => 'No se pudo actualizar el empleado.'];
        }

        if (!$updated) {
            if ($newPhoto !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $newPhoto);
            }
            if ($newCv !== '') {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $newCv);
            }
            return ['success' => false, 'message' => 'No se pudo actualizar el empleado.'];
        }

        $defaultFiles = ['user-default.jpg', 'cv_default.pdf'];
        if ($newPhoto !== '' && !in_array(basename($currentPhoto), $defaultFiles, true)) {
            $this->fileStorage->deleteFileIfExists($baseDirectory, $currentPhoto);
        }
        if ($newCv !== '' && !in_array(basename($currentCv), $defaultFiles, true)) {
            $this->fileStorage->deleteFileIfExists($baseDirectory, $currentCv);
        }

        return ['success' => true, 'message' => 'Empleado actualizado exitosamente.'];
    }

    public function deleteEmployee(int $id, string $baseDirectory)
    {
        $employeeId = $id;
        if ($employeeId <= 0) {
            return false;
        }

        $files = $this->employeeRepository->findFilesById($employeeId);
        if ($files !== null) {
            $defaultFiles = ['user-default.jpg', 'cv_default.pdf'];
            $foto = isset($files['Foto']) ? $files['Foto'] : '';
            $cv   = isset($files['CV'])   ? $files['CV']   : '';
            if ($foto !== '' && !in_array(basename($foto), $defaultFiles, true)) {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $foto);
            }
            if ($cv !== '' && !in_array(basename($cv), $defaultFiles, true)) {
                $this->fileStorage->deleteFileIfExists($baseDirectory, $cv);
            }
        }

        return $this->employeeRepository->deleteById($employeeId);
    }

    private function validateEmployeeData(array $data)
    {
        $primernombre = trim((string)($data['primernombre'] ?? ''));
        $primerapellido = trim((string)($data['primerapellido'] ?? ''));
        $segundoapellido = trim((string)($data['segundoapellido'] ?? ''));
        $idpuesto = (int)($data['idpuesto'] ?? 0);
        $fecha = (string)($data['fechadeingreso'] ?? '');

        if ($primernombre === '' || $primerapellido === '' || $segundoapellido === '') {
            return 'Debe completar los campos obligatorios del nombre y apellidos.';
        }

        if ($idpuesto <= 0) {
            return 'Debe seleccionar un puesto válido.';
        }

        if ($fecha === '' || !$this->isValidDate($fecha)) {
            return 'Debe seleccionar una fecha de ingreso válida.';
        }

        return null;
    }

    private function isValidDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function mergeUploadErrors(?string $photoError, ?string $cvError): string
    {
        $errors = [];
        if ($photoError !== null && trim($photoError) !== '') {
            $errors[] = 'Foto: ' . trim($photoError);
        }
        if ($cvError !== null && trim($cvError) !== '') {
            $errors[] = 'CV: ' . trim($cvError);
        }
        return $errors === [] ? 'No se pudo procesar la subida de archivos.' : implode(' ', $errors);
    }

    private function nullIfEmpty(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string)$value);
        return $trimmed === '' ? null : $trimmed;
    }
}
