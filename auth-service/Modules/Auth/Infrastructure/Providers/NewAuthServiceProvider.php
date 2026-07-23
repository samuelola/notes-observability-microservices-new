<?php

namespace Modules\Auth\Infrastructure\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;
use Modules\Auth\Infrastructure\Messaging\RabbitMQPublisher;
use Modules\Auth\Infrastructure\Persistence\Models\PersonalAccessToken;
use Modules\Auth\Infrastructure\Persistence\Repositories\AuthRepository;
use Modules\Auth\Infrastructure\Services\UserService;
use Modules\Auth\Infrastructure\Tracing\OpenTelemetryTracer;
use Modules\Auth\Presentation\Http\Middleware\CorrelationIdMiddleware;
use Modules\Auth\Presentation\Http\Middleware\TraceMiddleware;

class NewAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(EventPublisherInterface::class, RabbitMQPublisher::class);

        if (! app()->environment('testing')) {
            $this->app->singleton(
                OpenTelemetryTracer::class,
                fn () => new OpenTelemetryTracer
            );
        }

    }

    public function boot(Router $router): void
    {

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        $route = base_path('Modules/Auth/Presentation/routes/api.php');

        if (file_exists($route)) {
            $this->loadRoutesFrom($route);
        }

        $this->loadMigrationsFrom(
            base_path('Modules/Notes/Infrastructure/Persistence/database/migrations')
        );

        $router->aliasMiddleware(
            'auth.correlation',
            CorrelationIdMiddleware::class
        );
        $router->aliasMiddleware(
            'auth.tracing',
            TraceMiddleware::class
        );
        // $this->loadRoutesFrom(
        //     base_path(
        //         'Modules/Auth/Presentation/Routes/api.php'
        //     )
        // );

        // $this->loadRoutesFrom(...);

        // $this->loadViewsFrom(...);

        // $this->loadTranslationsFrom(...);

        // $this->loadMigrationsFrom(...);

    }
}
