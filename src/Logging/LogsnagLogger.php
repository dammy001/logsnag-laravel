<?php

namespace Damilaredev\LogsnagLaravel\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Logger;

class LogsnagLogger
{
    protected string $dateFormat = 'Y-m-d H:i:s';

    public function __invoke(array $config): Logger
    {
        $handler = new LogsnagHandler(
            channel: $config['channel'],
            notify: $config['notify'] ?? false,
            level: Logger::toMonologLevel($config['level'] ?? 'debug'),
        );

        $handler->setMinimumReportLogLevel(Level::Debug);

        if (! isset($config['formatter'])) {
            $handler->setFormatter($this->formatter());
        } elseif (class_exists($config['formatter']) && $config['formatter'] !== 'default') {
            $handler->setFormatter(app()->make($config['formatter'], $config['formatter_with'] ?? []));
        }

        return tap(new Logger('logsnag'), fn (Logger $logger) => $logger->pushHandler($handler));
    }

    protected function formatter(): LineFormatter
    {
        return new LineFormatter(
            null,
            $this->dateFormat,
            true,
            true,
            true
        );
    }
}
