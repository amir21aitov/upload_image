<?php

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\RegistrationRequest;

final readonly class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(RegistrationRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: strtolower(trim($request->validated('email'))),
            password: $request->validated('password'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
