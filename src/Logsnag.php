<?php

namespace Damilaredev\LogsnagLaravel;

use Damilaredev\LogsnagLaravel\ValueObjects\LogsnagRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class Logsnag implements \Damilaredev\LogsnagLaravel\Contracts\Logsnag
{
    public function __construct(protected readonly PendingRequest $client)
    {
    }

    public function log(LogsnagRequest $request): void
    {
        try {
            $this->client
                ->post('log', $request->attributes()->except(['onError', 'onSuccess'])->all())
                ->onError(callback: fn (Response $response) => $request->onError instanceof \Closure
                    ? call_user_func($request->onError, $response) : true
                );
        } catch (ConnectionException) {
        }
    }
}
