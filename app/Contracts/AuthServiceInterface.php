<?php

namespace App\Contracts;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\Requests\Auth\VerifyRequest;
use App\Models\User;

interface AuthServiceInterface
{
    public function register(RegistrationRequest $request): array;

    public function verifyOtp(VerifyRequest $request): User;

    public function login(LoginRequest $request): array;
}
