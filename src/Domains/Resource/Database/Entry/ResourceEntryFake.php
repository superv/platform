<?php

namespace SuperV\Platform\Domains\Resource\Database\Entry;

use Closure;
use SuperV\Platform\Domains\Resource\Fake;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Exceptions\PlatformException;

class ResourceEntryFake
{
    /** @return \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract|\Illuminate\Support\Collection */
    public static function make($resource, array $overrides = [], int $number = 1, Closure $callback = null)
    {
        if (is_string($resource)) {
            $resource = ResourceFactory::make($resource);
        }

        if ($resource instanceof Resource) {
            if ($number > 1) {
                $fakes = collect(range(1, $number))
                    ->map(function () use ($callback, $resource, $overrides) {
                        return static::make($resource, $overrides, 1, $callback);
                    })
                    ->all();

                return $fakes;
            }

            return Fake::create($resource, $overrides, $callback);
        }

        PlatformException::fail("Can not fake, resource not found");
    }
}