<?php

namespace App\Domain\Models;

class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $usuario,
        public readonly ?string $password,
        public readonly ?string $correo,
        public readonly bool $isAdmin = false,
        public readonly ?string $rememberToken = null,
        public readonly ?string $rememberTokenExpires = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)($row['ID'] ?? 0),
            usuario: (string)($row['Nombreusuario'] ?? ''),
            password: (isset($row['Password']) && $row['Password'] !== '') ? (string)$row['Password'] : null,
            correo: (isset($row['Correo']) && $row['Correo'] !== '') ? (string)$row['Correo'] : null,
            isAdmin: (bool)($row['is_admin'] ?? false),
            rememberToken: $row['remember_token'] ?? null,
            rememberTokenExpires: $row['remember_token_expires'] ?? null,
        );
    }

    // omits password
    public function toArray(): array
    {
        return [
            'ID'            => $this->id,
            'Nombreusuario' => $this->usuario,
            'Correo'        => $this->correo ?? '',
        ];
    }
}
