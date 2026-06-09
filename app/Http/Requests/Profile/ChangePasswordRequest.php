<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\Request;

class ChangePasswordRequest extends Request
{
    public function __construct(
        public readonly int    $userId,
        public readonly string $currentPassword,
        public readonly string $newPassword,
        public readonly string $confirmNewPassword
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            self::int($data, 'userId'),
            self::str($data, 'currentPassword'),
            self::str($data, 'newPassword'),
            self::str($data, 'confirmNewPassword')
        );
    }

    public static function forUser(int $userId, array $data): static
    {
        return new static(
            $userId,
            self::str($data, 'currentPassword'),
            self::str($data, 'newPassword'),
            self::str($data, 'confirmNewPassword')
        );
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->userId <= 0) {
            $errors['userId'] = 'Sesión de usuario no válida.';
        }
        if ($this->currentPassword === '') {
            $errors['currentPassword'] = 'Debe ingresar su contraseña actual.';
        }
        if ($this->newPassword === '') {
            $errors['newPassword'] = 'La nueva contraseña es obligatoria.';
        } elseif (strlen($this->newPassword) < 8) {
            $errors['newPassword'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }
        if ($this->newPassword !== $this->confirmNewPassword) {
            $errors['confirmNewPassword'] = 'La confirmación de la nueva contraseña no coincide.';
        }

        return $errors;
    }
}
