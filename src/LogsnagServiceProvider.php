<?php

namespace Damilaredev\LogsnagLaravel;

use Damilaredev\LogsnagLaravel\Commands\TestCommand;
use Damilaredev\LogsnagLaravel\Contracts\Logsnag as LogsnagContract;
use Damilaredev\LogsnagLaravel\Logging\LogsnagHandler;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Monolog\Level;
use Monolog\Logger;

class LogsnagServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->registerConfig();

        $config = $this->app->make('config');

        $client = $this->app->make(PendingRequest::class)->asJson()
            ->baseUrl($config->get('logsnag.base_url'))
            ->withToken($config->get('logsnag.token'))
            ->acceptJson()
            ->timeout(5);

        $this->app->singleton(Logsnag::class, fn (Application $app) => new Logsnag($client));
        $this->app->bind(LogsnagContract::class, Logsnag::class);

        $this->registerLogHandler();
    }

    public function boot(): void
    {
        $this->registerCommands();
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/logsnag.php', 'logsnag');
    }

    public function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->registerCommand();
        }
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/logsnag.php' => config_path('logsnag.php'),
        ], 'logsnag-config');
    }

    protected function registerCommand(): void
    {
        $this->commands([TestCommand::class]);
    }

    protected function registerLogHandler(): void
    {
        $this->app->singleton('logsnag.logger', function ($app) {
            $handler = new LogsnagHandler(
                $app['config']->get('logging.channels.logsnag.channel'),
                false,
            );

            $logLevelString = $app['config']->get('logging.channels.logsnag.level', 'error');

            $handler->setMinimumReportLogLevel($this->getLogLevel($logLevelString));

            return tap(
                new Logger('Logsnag'),
                fn (Logger $logger) => $logger->pushHandler($handler)
            );
        });

        Log::extend('logsnag', fn ($app) => $app['logsnag.logger']);
    }

    protected function getLogLevel(string $logLevelString): int
    {
        try {
            $logLevel = Level::fromName($logLevelString);
        } catch (Exception) {
            $logLevel = null;
        }

        if (! $logLevel) {
            throw new InvalidArgumentException("Invalid log level `{$logLevelString}` specified.");
        }

        return $logLevel->value;
    }

    public function provides(): array
    {
        return [Logsnag::class];
    }
}
