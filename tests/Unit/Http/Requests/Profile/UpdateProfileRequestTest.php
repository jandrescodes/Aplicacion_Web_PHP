<?php

namespace Tests\Unit\Http\Requests\Profile;

use App\Http\Requests\Profile\UpdateProfileRequest;
use PHPUnit\Framework\TestCase;

class UpdateProfileRequestTest extends TestCase
{
    private function valid(): array
    {
        return ['usuario' => 'jdoe', 'correo' => 'jdoe@example.com'];
    }

    public function test_forUser_assigns_session_user_id_not_from_post(): void
    {
        $req = UpdateProfileRequest::forUser(7, array_merge($this->valid(), ['userId' => 99]));

        $this->assertSame(7, $req->userId);
    }

    public function test_validate_passes_with_valid_data(): void
    {
        $errors = UpdateProfileRequest::forUser(1, $this->valid())->validate();

        $this->assertEmpty($errors);
    }

    public function test_validate_fails_when_usuario_empty(): void
    {
        $errors = UpdateProfileRequest::forUser(1, array_merge($this->valid(), ['usuario' => '']))->validate();

        $this->assertArrayHasKey('usuario', $errors);
    }

    public function test_validate_fails_when_correo_empty(): void
    {
        $errors = UpdateProfileRequest::forUser(1, array_merge($this->valid(), ['correo' => '']))->validate();

        $this->assertArrayHasKey('correo', $errors);
    }

    public function test_validate_fails_when_correo_invalid_format(): void
    {
        $errors = UpdateProfileRequest::forUser(1, array_merge($this->valid(), ['correo' => 'no-es-email']))->validate();

        $this->assertArrayHasKey('correo', $errors);
    }

    public function test_validate_fails_when_user_id_is_zero(): void
    {
        $errors = UpdateProfileRequest::forUser(0, $this->valid())->validate();

        $this->assertArrayHasKey('userId', $errors);
    }
}
