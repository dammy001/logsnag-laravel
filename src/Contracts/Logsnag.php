<?php

namespace Damilaredev\LogsnagLaravel\Contracts;

use Damilaredev\LogsnagLaravel\ValueObjects\LogsnagRequest;

interface Logsnag
{
    public function log(LogsnagRequest $request): void;
}
