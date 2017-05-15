<?php

use SuperV\Platform\Contracts\Container;

if (!function_exists('superv')) {
    /**
     * Get the available container instance.
     *
     * @param  string $abstract
     * @param  array  $parameters
     *
     * @return mixed|\Illuminate\Foundation\Application
     */
    function superv($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }
        
        return empty($parameters)
            ? Container::getInstance()->make($abstract)
            : Container::getInstance()->makeWith($abstract, $parameters);
    }
}
