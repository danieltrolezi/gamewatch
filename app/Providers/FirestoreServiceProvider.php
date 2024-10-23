<?php

namespace App\Providers;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\ServiceProvider;

class FirestoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirestoreClient::class, function ($app) {
            $config = [
                'projectId' => config('database.connections.firestore.project_id'),
                'database'  => config('database.connections.firestore.database'),
            ];

            return new FirestoreClient($config);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
