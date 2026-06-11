<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class OtpResendThrottledException extends HttpException
{
    public function __construct(int $retryAfter)
    {
        parent::__construct(
            429,
            'Please wait before requesting a new code',
            null,
            ['Retry-After' => (string) $retryAfter],
        );
    }
}
