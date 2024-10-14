<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Redis;

class HealthCheckService
{
    private const STATUS_OK = 'OK';
    private const STATUS_FAIL = 'FAIL';
    private const SERVICE_ENVIRONMENT = 'Environment';
    private const SERVICE_FIRESTORE = 'Firestore';
    private const SERVICE_REDIS = 'Redis';

    public function run(): array
    {
        $checks = [
            $this->checkEnvironment(),
            $this->checkFirestore(),
            $this->checkRedis(),
        ];

        $allChecksPass = collect($checks)->every(
            fn($check) => $check['status'] === self::STATUS_OK
        );

        return [
            'pass'   => $allChecksPass,
            'checks' => $checks,
        ];
    }

    /**
     * @param string $service
     * @param string $status
     * @param array $data
     * @return array
     */
    private function makeSucessfulCheck(string $service): array
    {
        return [
            'service' => $service,
            'status'  => self::STATUS_OK,
        ];
    }

    /**
     * @param string $service
     * @param string $error
     * @param array $data
     * @return array
     */
    private function makeFailedCheck(string $service, string $error): array
    {
        return [
            'service' => $service,
            'status'  => self::STATUS_FAIL,
            'error'   => $error
        ];
    }

    /**
     * @return array
     */
    private function checkEnvironment(): array
    {
        $environment = config('app.env');
        $expectedEnv = match (config('app.url')) {
            'http://gamewatch.local' => 'local',
            default                  => 'production'
        };

        if ($environment === $expectedEnv) {
            return $this->makeSucessfulCheck(self::SERVICE_ENVIRONMENT);
        }

        return $this->makeFailedCheck(
            self::SERVICE_ENVIRONMENT,
            "Expected \"{$expectedEnv}\" env, received \"{$environment}\".",
        );
    }

    /**
     * @return array
     */
    private function checkFirestore(): array
    {
        try {
            $firestore = resolve(FirestoreClient::class);
            $firestore->collection('test')->document('test')->snapshot();

            return $this->makeSucessfulCheck(self::SERVICE_FIRESTORE);
        } catch (\Exception $e) {
            return $this->makeFailedCheck(self::SERVICE_FIRESTORE, $e->getMessage());
        }
    }

    /**
     * @return array
     */
    private function checkRedis(): array
    {
        try {
            Redis::connection()->ping();

            return $this->makeSucessfulCheck(self::SERVICE_REDIS);
        } catch (\Exception $e) {
            return $this->makeFailedCheck(self::SERVICE_REDIS, $e->getMessage());
        }
    }
}
