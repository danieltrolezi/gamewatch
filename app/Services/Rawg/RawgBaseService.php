<?php

namespace App\Services\Rawg;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

abstract class RawgBaseService
{
    private const int CACHE_TTL = 24 * 60 * 60;

    protected string $apiKey;
    protected string $apiUrl;

    public function __construct(
        protected RawgFilterService $filterService,
        protected Client $client
    ) {
        $this->apiUrl = config('services.rawg.host') . '/api/';
        $this->apiKey = config('services.rawg.key');
    }

    /**
     * @param string $uri
     * @param array $data
     * @param string $method
     * @return array
     */
    protected function call(
        string $uri,
        array $data = ['query' => []],
        string $method = 'GET',
    ): array {
        $contents = $this->getCacheContents($uri, $data);

        if (empty($contents)) {
            $endpoint = $this->apiUrl . $uri;

            $res = $this->client->request($method, $endpoint, [
                'query' => array_merge($data['query'], [
                    'key' => $this->apiKey
                ])
            ]);

            $contents = $res->getBody()->getContents();
            $this->setCacheContents($uri, $data, $contents);
        }

        return json_decode($contents, true);
    }

    /**
     * @param string $uri
     * @param array $data
     * @return null|string
     */
    private function getCacheContents(string $uri, array $data): ?string
    {
        $key = $this->getCacheKey($uri, $data['query']);
        return Redis::get($key);
    }

    /**
     * @param string $uri
     * @param array $data
     * @param string $payload
     * @return void
     */
    private function setCacheContents(string $uri, array $data, string $contents): void
    {
        $key = $this->getCacheKey($uri, $data['query']);
        $result = Redis::setEx($key, self::CACHE_TTL, $contents);

        Log::debug(sprintf(
            'Redis cache set [key: %s, result: %s]',
            $key,
            $result
        ));
    }

    /**
     * @param string $uri
     * @param array $data
     * @return string
     */
    private function getCacheKey(string $uri, array $query): string
    {
        return sprintf(
            'rawg_%s_%s_',
            $uri,
            implode('_', $query)
        );
    }
}
