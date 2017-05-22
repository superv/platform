<?php

namespace nucleus {
    
    use SuperV\Nucleus\Domains\Entry\EntryManager;
    
    function fields($type)
    {
        return superv("fields.{$type}");
    }

}

namespace {
    
    use Illuminate\Container\Container;
    use SuperV\Platform\Support\Collection;


    function mysql_now() {
        return date('Y-m-d H:i:s');
    }
    
    /** @return \Predis\Client */
    function redis()
    {
        return superv('redis');
    }
    
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
    
    if (!function_exists('collect')) {
        /**
         * Create a collection from the given value.
         *
         * @param  mixed $value
         *
         * @return \SuperV\Platform\Support\Collection
         */
        function collect($value = null)
        {
            return new Collection($value);
        }
    }
}