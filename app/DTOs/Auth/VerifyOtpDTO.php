<?php

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\VerifyRequest;

final readonly class VerifyOtpDTO
{
    public function __construct(
        public string $email,
        public int $code,
    ) {}

    public static function fromRequest(VerifyRequest $request): self
    {
        return new self(
            email: strtolower(trim($request->validated('email'))),
            code: (int) $request->validated('code'),
        );
    }
}
