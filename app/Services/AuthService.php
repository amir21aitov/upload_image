<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidOtpException;
use App\Exceptions\OtpResendThrottledException;
use App\Exceptions\UserAlreadyExistsException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\UserNotVerifiedException;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\Requests\Auth\VerifyRequest;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthService implements AuthServiceInterface
{
    private const OTP_TTL_SECONDS = 300;
    private const OTP_RESEND_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 5;

    public function register(RegistrationRequest $request): array
    {
        $email = $this->normalizeEmail($request->email);

        if (!Cache::add($this->resendKey($email), 1, self::OTP_RESEND_SECONDS)) {
            Log::info('OTP resend throttled', ['email' => $email]);
            throw new OtpResendThrottledException(self::OTP_RESEND_SECONDS);
        }

        $user = User::query()->where('email', $email)->first();

        if ($user?->verified_at !== null) {
            Log::info('Registration attempt for already verified email', ['email' => $email]);
            throw new UserAlreadyExistsException();
        }

        $otpCode = random_int(100000, 999999);

        $user = DB::transaction(function () use ($user, $request, $email, $otpCode) {
            if ($user) {
                $user->update([
                    'name' => $request->validated('name'),
                    'password' => $request->validated('password'),
                ]);
            } else {
                $user = User::query()->create([
                    'name' => $request->validated('name'),
                    'email' => $email,
                    'password' => $request->validated('password'),
                ]);
            }

            Cache::put($this->otpKey($email), $otpCode, self::OTP_TTL_SECONDS);
            Cache::add($this->attemptsKey($email), 0, self::OTP_TTL_SECONDS);

            return $user;
        });

        DB::afterCommit(function () use ($email, $otpCode, $user) {
            Mail::to($email)->send(new OtpMail($otpCode));
            Log::info('User registered, OTP queued', ['user_id' => $user->id, 'email' => $email]);
        });

        return [
            'user_id' => $user->id,
            'resend_in' => self::OTP_RESEND_SECONDS,
        ];
    }

    public function verifyOtp(VerifyRequest $request): User
    {
        $email = $this->normalizeEmail($request->email);

        $user = User::query()->where('email', $email)->whereNull('verified_at')->first();

        if (!$user) {
            Log::warning('OTP verification for non-existent or already verified user', ['email' => $email]);
            throw new UserNotFoundException();
        }

        $attemptsKey = $this->attemptsKey($email);
        Cache::add($attemptsKey, 0, self::OTP_TTL_SECONDS);
        $attempts = (int) Cache::increment($attemptsKey);

        if ($attempts > self::OTP_MAX_ATTEMPTS) {
            $this->clearOtp($email);
            Log::warning('OTP brute-force blocked', ['email' => $email, 'attempts' => $attempts]);
            throw new InvalidOtpException('Too many attempts. Request a new code.');
        }

        $cached = Cache::get($this->otpKey($email));

        if (!$cached || !hash_equals((string) $cached, (string) $request->code)) {
            Log::info('Invalid OTP attempt', ['email' => $email, 'attempt' => $attempts]);
            throw new InvalidOtpException();
        }

        $this->clearOtp($email);

        $user->verified_at = now();
        $user->save();

        Log::info('User verified via OTP', ['user_id' => $user->id, 'email' => $email]);

        return $user;
    }

    public function login(LoginRequest $request): array
    {
        $email = $this->normalizeEmail($request->email);

        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            Log::info('Login attempt for non-existent email', ['email' => $email]);
            throw new InvalidCredentialsException();
        }

        if ($user->verified_at === null) {
            Log::info('Login attempt for unverified user', ['user_id' => $user->id]);
            throw new UserNotVerifiedException();
        }

        $token = auth('api')->attempt([
            'email' => $email,
            'password' => $request->password,
        ]);

        if (!$token) {
            Log::info('Failed login attempt (wrong password)', ['user_id' => $user->id]);
            throw new InvalidCredentialsException();
        }

        Log::info('User logged in', ['user_id' => $user->id]);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function otpKey(string $email): string
    {
        return "otp:{$email}";
    }

    private function attemptsKey(string $email): string
    {
        return "otp_attempts:{$email}";
    }

    private function resendKey(string $email): string
    {
        return "otp_resend:{$email}";
    }

    private function clearOtp(string $email): void
    {
        Cache::forget($this->otpKey($email));
        Cache::forget($this->attemptsKey($email));
    }
}
