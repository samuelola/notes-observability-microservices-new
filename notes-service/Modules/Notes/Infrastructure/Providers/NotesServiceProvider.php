<?php

namespace Modules\Notes\Infrastructure\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Application\Contracts\NoteServiceInterface;
use Modules\Notes\Application\Services\NoteService;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;
use Modules\Notes\Infrastructure\Cache\LaravelCache;
use Modules\Notes\Infrastructure\Events\LaravelEventDispatcher;
use Modules\Notes\Infrastructure\External\HttpAuthClient;
use Modules\Notes\Infrastructure\Persistence\Repositories\NoteRepository;
use Modules\Notes\Infrastructure\Storage\S3ImageStorage;
use Modules\Notes\Infrastructure\Tracing\OpenTelemetryTracer;
use Modules\Notes\Presentation\Http\Middleware\AuthenticateWithAuthService;
use Modules\Notes\Presentation\Http\Middleware\CheckUserInactivity;
use Modules\Notes\Presentation\Http\Middleware\CorrelationIdMiddleware;
use Modules\Notes\Presentation\Http\Middleware\TraceMiddleware;

class NotesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NoteRepositoryInterface::class, NoteRepository::class);
        $this->app->bind(NoteServiceInterface::class, NoteService::class);
        $this->app->bind(AuthClientInterface::class, HttpAuthClient::class);
        $this->app->singleton(
            OpenTelemetryTracer::class,
            fn () => new OpenTelemetryTracer
        );
        $this->app->bind(
            CacheInterface::class,
            LaravelCache::class
        );

        $this->app->bind(
            EventDispatcherInterface::class,
            LaravelEventDispatcher::class
        );

        $this->app->bind(
            ImageStorageInterface::class,
            S3ImageStorage::class
        );
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware(
            'note_inactive_owner',
            CheckUserInactivity::class
        );

        $router->aliasMiddleware(
            'auth.service',
            AuthenticateWithAuthService::class
        );

        $router->aliasMiddleware(
            'note.correlation',
            CorrelationIdMiddleware::class
        );

        $router->aliasMiddleware(
            'note.tracing',
            TraceMiddleware::class
        );

        $route = base_path('Modules/Notes/Presentation/routes/api.php');

        if (file_exists($route)) {
            $this->loadRoutesFrom($route);
        }

        $this->loadMigrationsFrom(
            base_path('Modules/Notes/Infrastructure/Persistence/database/migrations')
        );

        // $this->loadRoutesFrom(
        //     base_path(
        //         'Modules/Notes/Presentation/Routes/api.php'
        //     )
        // );

        // $this->loadRoutesFrom(...);

        // $this->loadViewsFrom(...);

        // $this->loadTranslationsFrom(...);

        // $this->loadMigrationsFrom(...);

    }
}
