<?php

namespace Damilaredev\LogsnagLaravel\Commands;

use Composer\InstalledVersions;
use Damilaredev\LogsnagLaravel\Contracts\Logsnag;
use Damilaredev\LogsnagLaravel\ValueObjects\LogsnagRequest;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;

class TestCommand extends Command
{
    protected $signature = 'logsnag:test';

    protected $description = 'Send a test notification to Logsnag';

    protected Repository $config;

    public function __invoke(): void
    {
        $this->config = $this->getLaravel()['config'];

        $this->checkLogsnagToken();

        if ($this->getLaravel()->bound('log')) {
            $this->checkLogsnagLoggerConfig();
        }

        $this->sendTestMessage();
    }

    protected function checkLogsnagToken(): static
    {
        $message = empty($this->config->get('logsnag.token'))
            ? '❌ Logsnag token not specified. Make sure you specify a value in the `token` key of the `logsnag` config file.'
            : '✅ Logsnag token specified';

        $this->info($message);

        return $this;
    }

    protected function checkLogsnagLoggerConfig(): static
    {
        $defaultLogChannel = $this->config->get('logging.default');

        $activeStack = $this->config->get("logging.channels.$defaultLogChannel");

        if (is_null($activeStack)) {
            $this->info("❌ The default logging channel `$defaultLogChannel` is not configured in the `logging` config file");
        }

        if (is_null($this->config->get('logging.channels.logsnag'))) {
            $this->info('❌ There is no logging channel named `logsnag` in the `logging` config file');
        }

        if (is_null($this->config->get('logging.channels.logsnag.channel'))) {
            $this->info('❌ The `channel` defined in the `logsnag` config file is not configured.');
        }

        if ($this->config->get('logging.channels.logsnag.driver') !== 'custom') {
            $this->info('❌ The `logsnag` logging channel defined in the `logging` config file is not set to `custom`.');
        }

        $this->info('✅ Logsnag logging driver was configured correctly.');

        return $this;
    }

    protected function sendTestMessage(): void
    {
        $message = 'This is a message to test if integration with Logsnag works.';

        $this->getLaravel()->make(Logsnag::class)->log(
            new LogsnagRequest(
                channel: $this->config->get('logging.channels.logsnag.channel'),
                event: $message,
                notify: false,
                onError: function ($response) {
                    $this->line('');

                    if ($response instanceof Response) {
                        $this->error('An error occurred: '.$response->reason());
                    }

                    $this->warn('Make sure that your token is correct and that you have a valid plan.');
                    $this->info('');
                    $this->info('For more info visit the docs on https://docs.logsnag.com/');

                    $this->line('');
                    $this->line('Extra info');
                    $this->table([], [
                        ['Platform', PHP_OS],
                        ['PHP', phpversion()],
                        ['Laravel', app()->version()],
                        ['logsnag/laravel', InstalledVersions::getVersion('logsnag/laravel')],
                    ]);

                },
                tags: [
                    'test' => 'yes',
                ]
            )
        );

        $this->info('We tried to send a message to Logsnag. Please check if it arrived!');
    }
}
