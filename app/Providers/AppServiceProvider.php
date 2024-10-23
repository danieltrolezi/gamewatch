<?php

namespace App\Providers;

use App\Guards\JwtGuard;
use App\Repositories\UserRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\RequestReceived;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->setJwtGuard();
        $this->setRateLimit();
        $this->setLogContext();
        $this->setRequestLogging();
    }

    private function setJwtGuard(): void
    {
        Auth::provider('firestore', function ($app, array $config) {
            return new FirestoreUserProvider(
                $app->make(UserRepository::class)
            );
        });

        Auth::extend('jwt', function (Application $app, string $name, array $config) {
            return new JwtGuard(
                Auth::createUserProvider($config['provider']),
                $app['request']
            );
        });
    }

    private function setRateLimit(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if ($request->user()) {
                $user = $request->user();

                return $user->isRoot()
                    ? Limit::none()
                    : Limit::perMinute(config('app.rate_limit.user'))->by($user->id);
            }

            return Limit::perMinute(config('app.rate_limit.guest'))->by($request->ip());
        });

        RateLimiter::for('discord', function (Request $request) {
            $user = $request->get('user');

            if (!empty($user)) {
                return Limit::perMinute(config('app.rate_limit.user'))->by($user['id']);
            }

            return Limit::perMinute(config('app.rate_limit.discord'));
        });
    }

    private function setLogContext(): void
    {
        $common = [
            'app' => Str::slug(config('app.name')),
            'env' => config('app.env'),
        ];

        $this->app['events']->listen(RequestReceived::class, function (RequestReceived $event) use ($common) {
            $requestId = $event->request->header('X-Request-ID', (string) Str::uuid());

            Log::shareContext(array_merge($common, [
                'request_id' => $requestId
            ]));
        });

        $this->app['events']->listen(CommandStarting::class, function (CommandStarting $event) use ($common) {
            Log::shareContext(array_merge($common, [
                'command' => $event->command,
            ]));
        });
    }

    private function setRequestLogging(): void
    {
        $this->app['events']->listen(RequestHandled::class, function (RequestHandled $event) {
            $responseContent = '{***}';
            $protectedRoutes = ['/api/auth/login'];

            if (!in_array($event->request->getPathInfo(), $protectedRoutes)) {
                $responseContent = strlen($event->response->getContent()) > 120
                ? substr($event->response->getContent(), 0, 120) . '...'
                : $event->response->getContent();
            }

            Log::info('Request & Response', [
                'method'     => $event->request->method(),
                'url'        => $event->request->fullUrl(),
                'status'     => $event->response->getStatusCode(),
                'ip'         => $event->request->ip(),
                'client_ip'  => $event->request->getClientIp(),
                'user_id'    => $event->request->user()?->id,
                'user_agent' => $event->request->userAgent(),
                'referer'    => $event->request->header('Referer'),
                'response'   => $responseContent,
            ]);
        });
    }
}
