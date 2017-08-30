<?php

namespace SuperV\Platform\Support;

/**
 * Class Hydrator
 * Builds and object setting properties from a parameters,
 * array by using available setter methods .
 */
class Hydrator
{
    public function hydrate($object, array $parameters)
    {
        foreach ($parameters as $parameter => $value) {
            $method = camel_case('set_'.$parameter);

            if (method_exists($object, $method)) {
                $object->{$method}($value);
            }
        }

        return $object;
    }
}
