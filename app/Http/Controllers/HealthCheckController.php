<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController extends Controller
{
    public function __invoke()
    {
        $healthCheckService = resolve(HealthCheckService::class);
        $result = $healthCheckService->run();

        $status = $result['pass']
            ? Response::HTTP_OK
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json($result, $status);
    }
}
