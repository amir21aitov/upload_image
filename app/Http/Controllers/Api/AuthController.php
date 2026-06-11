<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\VerifyOtpDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\Requests\Auth\VerifyRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    public function register(RegistrationRequest $request): JsonResponse
    {
        return response()->json(
            $this->authService->register(RegisterDTO::fromRequest($request)),
            Response::HTTP_CREATED
        );
    }

    public function verifyOtp(VerifyRequest $request): JsonResponse
    {
        return response()->json(
            $this->authService->verifyOtp(VerifyOtpDTO::fromRequest($request)),
            Response::HTTP_OK
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json(
            $this->authService->login(LoginDTO::fromRequest($request)),
            Response::HTTP_OK
        );
    }
}
