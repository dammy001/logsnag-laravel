<?php

namespace Damilaredev\LogsnagLaravel\ValueObjects;

use Closure;
use Illuminate\Support\Collection;

class LogsnagRequest
{
    public function __construct(
        public readonly string $channel,
        public readonly string $event,
        public readonly bool $notify,
        public readonly ?string $description = null,
        public readonly ?string $icon = null,
        public readonly ?Closure $onError = null,
        public readonly ?array $tags = []
    ) {
    }

    public function attributes(): Collection
    {
        return Collection::make([
            ...array_filter(get_object_vars($this)),
            'project' => config('logsnag.project'),
        ]);
    }
}
