<?php

namespace SuperV\Platform\Support\Concerns;

trait Hydratable
{
    public function hydrate(array $parameters)
    {
        foreach ($parameters as $parameter => $value) {
            $method = camel_case('set_'.$parameter);

            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }

        return $this;
    }
}