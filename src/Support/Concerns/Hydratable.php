<?php

namespace SuperV\Platform\Support\Concerns;

trait Hydratable
{
    public function hydrate(array $parameters, bool $overrideDefault = true)
    {
        $parameters = array_filter_null($parameters);

        if (empty($parameters)) {
            return $this;
        }

        if (isset($this->hydratables)) {
            $parameters = array_intersect_key($parameters, array_flip($this->hydratables));
        }

        foreach ($parameters as $parameter => $value) {
            if (is_null($value)) {
                continue;
            }

            if (!$overrideDefault && !is_null($this->$parameter))
                continue;

            if (method_exists($this, $method = camel_case('set_'.$parameter))) {
                $this->{$method}($value);
            } elseif (property_exists($this, $parameter)) {
                $this->$parameter = $value;
            } elseif (property_exists($this, $camelCaseParameter = camel_case($parameter))) {
                $this->$camelCaseParameter = $value;
            }
        }

        return $this;
    }
}