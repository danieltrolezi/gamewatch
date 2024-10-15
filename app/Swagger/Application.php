<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(title: APP_NAME, version: APP_VERSION)]
#[OA\Server(url: APP_URL)]
#[OA\OpenApi(
    security: [
        ['bearerAuth' => []]
    ]
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    in: 'header',
    name: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
)]
class Application
{
    #[OA\Tag(
        name: 'application',
        description: 'health and other application routes'
    )]
    #[OA\Tag(
        name: 'admin',
        description: 'admin panel routes'
    )]
    #[OA\Tag(
        name: 'domain',
        description: 'rawg domain routes'
    )]
    #[OA\Tag(
        name: 'games',
        description: 'rawg games routes'
    )]
    #[OA\Tag(
        name: 'auth',
        description: 'authentication routes'
    )]
    #[OA\Tag(
        name: 'account',
        description: 'account management routes'
    )]
    /**
     * @return null
     */
    public function tags(): null
    {
        return null;
    }
}
