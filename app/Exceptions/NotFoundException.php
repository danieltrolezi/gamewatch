<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotFoundException extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_NOT_FOUND,
            message: 'Not Found.'
        );
    }
}
