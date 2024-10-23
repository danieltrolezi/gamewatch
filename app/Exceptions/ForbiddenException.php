<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForbiddenException extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_FORBIDDEN,
            message: 'Forbidden'
        );
    }
}
