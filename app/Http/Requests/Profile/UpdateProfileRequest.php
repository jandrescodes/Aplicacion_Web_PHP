<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\Request;

class UpdateProfileRequest extends Request
{
    public function __construct(
        public readonly int    $userId,
        public readonly string $usuario,
        public readonly string $correo
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            self::int($data, 'userId'),
            self::str($data, 'usuario'),
            self::str($data, 'correo')
        );
    }

    public static function forUser(int $userId, array $data): static
    {
        return new static(
            $userId,
            self::str($data, 'usuario'),
            self::str($data, 'correo')
        );
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->userId <= 0) {
            $errors['userId'] = 'Sesión de usuario no válida.';
        }
        if ($this->usuario === '') {
            $errors['usuario'] = 'El usuario es obligatorio.';
        }
        if ($this->correo === '') {
            $errors['correo'] = 'El correo es obligatorio.';
        } elseif (!filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'Debe ingresar un correo válido.';
        }

        return $errors;
    }
}
