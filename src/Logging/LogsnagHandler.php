<?php

namespace Damilaredev\LogsnagLaravel\Logging;

use Damilaredev\LogsnagLaravel\Facade\Logsnag;
use Damilaredev\LogsnagLaravel\ValueObjects\LogsnagRequest;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

class LogsnagHandler extends AbstractProcessingHandler
{
    protected int $minimumReportLogLevel;

    public function __construct(
        protected readonly string $channel,
        protected readonly bool $notify,
        protected readonly bool $ignoreException = true,
        Level $level = Level::Debug,
        bool $bubble = true
    ) {
        $this->minimumReportLogLevel = Level::Debug->value;

        parent::__construct($level, $bubble);
    }

    public function setMinimumReportLogLevel(int|Level $level): void
    {
        $level = $level instanceof \BackedEnum ? $level->value : $level;

        if (! in_array($level, Level::VALUES)) {
            throw new InvalidArgumentException('The given minimum log level is not supported.');
        }

        $this->minimumReportLogLevel = $level;
    }

    protected function write(LogRecord $record): void
    {
        if (! $this->shouldReport($record->toArray())) {
            return;
        }

        Logsnag::log(
            new LogsnagRequest(
                channel: $this->channel,
                event: $this->getFormatter()->format($record),
                notify: $this->notify,
                onError: ! $this->ignoreException ? function ($response) {
                    if ($response instanceof Response) {
                        throw $response->toException();
                    }
                }
                    : fn ($response) => true,
                tags: $record->toArray()['extra']
            )
        );
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        parent::setFormatter($formatter);

        return $this;
    }

    protected function shouldReport(array $report): bool
    {
        if (! config('logsnag.token') || ! config('logsnag.enabled')) {
            return false;
        }

        return $this->hasException($report) || $this->hasValidLogLevel($report);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function hasException(array $report): bool
    {
        $context = $report['context'];

        return isset($context['exception']) && $context['exception'] instanceof Throwable;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function hasValidLogLevel(array $report): bool
    {
        return $report['level'] >= $this->minimumReportLogLevel;
    }
}
