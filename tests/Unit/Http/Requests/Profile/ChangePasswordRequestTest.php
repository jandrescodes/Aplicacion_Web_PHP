<?php

namespace Tests\Unit\Http\Requests\Profile;

use App\Http\Requests\Profile\ChangePasswordRequest;
use PHPUnit\Framework\TestCase;

class ChangePasswordRequestTest extends TestCase
{
    private function valid(): array
    {
        return [
            'currentPassword'    => 'actual123',
            'newPassword'        => 'nueva12345',
            'confirmNewPassword' => 'nueva12345',
        ];
    }

    public function test_forUser_assigns_session_user_id_not_from_post(): void
    {
        $req = ChangePasswordRequest::forUser(5, array_merge($this->valid(), ['userId' => 99]));

        $this->assertSame(5, $req->userId);
    }

    public function test_validate_passes_with_valid_data(): void
    {
        $errors = ChangePasswordRequest::forUser(1, $this->valid())->validate();

        $this->assertEmpty($errors);
    }

    public function test_validate_fails_when_user_id_is_zero(): void
    {
        $errors = ChangePasswordRequest::forUser(0, $this->valid())->validate();

        $this->assertArrayHasKey('userId', $errors);
    }

    public function test_validate_fails_when_current_password_empty(): void
    {
        $errors = ChangePasswordRequest::forUser(1, array_merge($this->valid(), ['currentPassword' => '']))->validate();

        $this->assertArrayHasKey('currentPassword', $errors);
    }

    public function test_validate_fails_when_new_password_empty(): void
    {
        $errors = ChangePasswordRequest::forUser(1, array_merge($this->valid(), ['newPassword' => '', 'confirmNewPassword' => '']))->validate();

        $this->assertArrayHasKey('newPassword', $errors);
    }

    public function test_validate_fails_when_new_password_too_short(): void
    {
        $errors = ChangePasswordRequest::forUser(1, array_merge($this->valid(), ['newPassword' => '1234567', 'confirmNewPassword' => '1234567']))->validate();

        $this->assertArrayHasKey('newPassword', $errors);
    }

    public function test_validate_fails_when_passwords_do_not_match(): void
    {
        $errors = ChangePasswordRequest::forUser(1, array_merge($this->valid(), ['confirmNewPassword' => 'diferente1']))->validate();

        $this->assertArrayHasKey('confirmNewPassword', $errors);
    }
}
