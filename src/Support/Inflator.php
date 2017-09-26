<?php

namespace SuperV\Platform\Support;

class Inflator
{
    public function inflate($object, array $parameters)
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
