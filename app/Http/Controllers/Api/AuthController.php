<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\Requests\Auth\VerifyRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    public function register(RegistrationRequest $request): JsonResponse
    {
        return response()->json($this->authService->register($request), Response::HTTP_CREATED);
    }


    public function verifyOtp(VerifyRequest $request): JsonResponse
    {
        return response()->json($this->authService->verifyOtp($request), Response::HTTP_OK);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json($this->authService->login($request), Response::HTTP_OK);
    }
}
