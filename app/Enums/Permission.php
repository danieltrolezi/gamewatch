<?php

namespace App\Enums;

use App\Enums\Traits\BaseEnum;
use OpenApi\Attributes as OA;

#[OA\Schema(type: 'string')]
enum Permission: string
{
    use BaseEnum;

    case Default = 'default';
    case Root = 'root';
}
