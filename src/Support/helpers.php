<?php

use Illuminate\Container\Container;
use SuperV\Platform\Support\Collection;

function mysql_now()
{
    return date('Y-m-d H:i:s');
}

/** @return \Predis\Client */
function redis()
{
    return superv('redis');
}

function array_set_if_not($condition, &$array, $key, $value)
{
    array_set_if(! $condition, $array, $key, $value);
}

function array_set_if($condition, &$array, $key, $value)
{
    if ($condition) {
        array_set($array, $key, $value);
    }
}

function html_attributes($attributes)
{
    $html = [];

    foreach ((array) $attributes as $key => $value) {
        $element = html_attribute_element($key, $value);

        if (! is_null($element)) {
            $html[] = $element;
        }
    }

    return count($html) > 0 ? ' '.implode(' ', $html) : '';
}

function html_attribute_element($key, $value)
{

    /*
     * For numeric keys we will assume that the value is a boolean attribute
     * where the presence of the attribute represents a true value and the
     * absence represents a false value.
     * This will convert HTML attributes such as "required" to a correct
     * form instead of using incorrect numerics.
     */
    if (is_numeric($key)) {
        return $value;
    }

    // Treat boolean attributes as HTML properties
    if (is_bool($value) && $key != 'value') {
        return $value ? $key : '';
    }

    if (! is_null($value)) {
        return $key.'="'.e($value).'"';
    }
}

if (! function_exists('superv')) {
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

if (! function_exists('collect')) {
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
