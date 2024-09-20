<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidCredentialsException extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_UNAUTHORIZED,
            message: 'The provided credentials do not match our records.'
        );
    }
}
