<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class HealthCheckController extends Controller
{
    #[OA\Get(
        path: '/api/up',
        tags: ['application'],
        security: [],
        responses: [
            new OA\Response(response: 200, description: 'OK')
        ]
    )]
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
