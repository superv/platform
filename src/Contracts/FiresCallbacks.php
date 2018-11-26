<?php

namespace SuperV\Platform\Contracts;

use Closure;

/**
 * Class Field
 * No closures allowed here..
 *
 * @package SuperV\Platform\Domains\Resource\Field
 */
interface FiresCallbacks
{
    public function on($trigger, ?Closure $callback);

    public function fire($trigger, array $parameters = []);

    public function hasCallback($trigger);

    public function getCallback($trigger): ?Closure;
}