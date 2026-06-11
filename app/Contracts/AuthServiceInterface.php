<?php

namespace App\Contracts;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\VerifyOtpDTO;
use App\Models\User;

interface AuthServiceInterface
{
    public function register(RegisterDTO $dto): array;

    public function verifyOtp(VerifyOtpDTO $dto): User;

    public function login(LoginDTO $dto): array;
}
