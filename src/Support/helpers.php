<?php

use Illuminate\Container\Container;
use SuperV\Platform\Domains\Feature\FeatureBus;
use SuperV\Platform\Support\Collection;
use SuperV\Platform\Support\Decorator;

function platform_path($path = null)
{
    return 'vendor/superv/platform'.(is_null($path) ? '' : DIRECTORY_SEPARATOR.$path);
}

/**
 * @param null $handler
 * @return FeatureBus
 */
function feature($handler = null)
{
    if ($handler) {
        return \Feature::handler($handler);
    }

    return app(FeatureBus::class);
}

function reload_env()
{
    foreach (file(base_path('.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        // Check for # comments.
        if (! starts_with($line, '#')) {
            if (starts_with($line, 'SUPERV_')) {
                putenv($line);
            }
        }
    }
}

function mysql_now()
{
    return date('Y-m-d H:i:s');
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

    foreach ((array)$attributes as $key => $value) {
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
     * @return mixed|\Illuminate\Foundation\Application
     */
    function superv($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        // handle platform bindings
        if (! preg_match('/[^A-Za-z._\-]/', $abstract)) {
            $abstract = "superv.{$abstract}";
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
     * @return \SuperV\Platform\Support\Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

function uuid()
{
    $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();

    return str_replace('-', '', $uuid);
}