<?php

namespace Damilaredev\LogsnagLaravel\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for Logsnag.
 *
 * @see \Damilaredev\LogsnagLaravel\Logsnag
 *
 * @method static void log(\Damilaredev\LogsnagLaravel\ValueObjects\LogsnagRequest $request): void
 */
class Logsnag extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Damilaredev\LogsnagLaravel\Logsnag::class;
    }
}
