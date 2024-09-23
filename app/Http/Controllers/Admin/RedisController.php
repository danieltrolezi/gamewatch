<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use OpenApi\Attributes as OA;

class RedisController extends Controller
{
    #[OA\Get(
        path: '/api/admin/redis/keys',
        tags: ['admin'],
        responses: [
            new OA\Response(response: 200, description: 'List of Redis keys and values')
        ]
    )]
    public function keys(): JsonResponse
    {
        $redis = Redis::connection(config('database.redis.connection'));
        $prefix = config('database.redis.options.prefix');
        $keys = $redis->keys('*');
        $data = [];

        foreach ($keys as $key) {
            $value = $redis->get(
                str_replace($prefix, '', $key)
            );

            $data[$key] = substr($value, 0, 100) . '...';
        }

        return response()->json($data);
    }
}
