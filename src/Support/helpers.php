<?php

use Illuminate\Container\Container;
use SuperV\Platform\Support\Collection;
use SuperV\Platform\Support\Decorator;

function platform_path($path = null)
{

    return 'vendor/superv/platform'.(is_null($path) ? '' : DIRECTORY_SEPARATOR.$path);
}

function reload_env()
{
    foreach (file(base_path('.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        // Check for # comments.
        if (! starts_with($line, '#')) {
            putenv($line);
        }
    }
}

function mysql_now()
{
    return date('Y-m-d H:i:s');
}

/** @return \Predis\Client */
function redis()
{
    return app('redis');
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
     *
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
     *
     * @return \SuperV\Platform\Support\Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (! function_exists('decorate')) {
    function decorate($presentable)
    {
        return (new Decorator())->decorate($presentable);
    }
}

function uuid()
{
    $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();

    return str_replace('-', '', $uuid);
}

function pkey()
{
    return "-----BEGIN RSA PRIVATE KEY-----\nMIIEpQIBAAKCAQEA114tqz+bP6aVOTbF1H6YSiazSUg5NwRWfodRuEHCl0JvF1jT\nqJpnqdHRhrj0kYsPGo2S3QR9fgx88HGAB+pzTBmk7TdCXh1BlsO5t/YrIrNnEgzV\nbkL3exNRExl6FYNUJuaNQUL6ofh7mWXbTvzVNY6RI3+feAKyZ0YvBcLdCmiU1P1j\nLuywlRfIAHQiL9kT0+x6fDWhn1L80NpZY0PDopzIY+MsGCzatQ51iTvGWnUyG+vz\nQaBTQUBaBKTemfLoieUm7V0PxfSBoVeranB1bW4oa4YFRMN7wt+UNkfs0VARmeTw\njidd7dxjAU0DjH7enDRksInIan0clgWKAZ0e9wIDAQABAoIBACX+vXvayt7d2rGv\nUhN9Cgz66uJg1Yc5QrEr2mTxZQ0ecU7jIuQ56VPyak6kTrFmoRGELIbFhgY42cnp\nnDbJS/H/DDO1bgYAj1Oz0A3+ZfnHQMXUccr0EYkrDUCbXAhzlOMQBZef42uz1Mpp\nR9mVjV/XeQ+UMmQPxb2miiEwEaMGIWRB2To/1PS6JBqCylUyBUJ07wp+1hjWgDhp\n2g0SiOE5RyKf0qCmqL88m9sPbUV6WOcBXMfSWUh0jn32/nIAn5i73XtTLpcIGaRe\nRvtamoMK2pPc5GWNfr5gLdTeOhzav91MW3Egw3jD0WniBFjEDSX8atRdcegcEjU3\noFunb1ECgYEA83Au0qtzTsJlPj6UXgUU3UmC6DUCPlQ4zL2XupmJvuGHKIZvF94d\n0QqE2lFCmEcdH9JR+EHlyeT3496tOKaWSNE1Fjf6S/K9LzELbVVWt8F8a51tA07g\nmYu+K9ZVQeZSe2heb1KsXk7FXy7ORcYE0BpRWY/yWovSHy/WoMhSDxUCgYEA4nsv\nxu9poo1qHfVEUHS2E6KWLO1PGI0GxLaHsQMau9NTWNZY0TdUOJvC4mOE+VvX/yCW\n7f9wHd4rK2MW29IrkTX57K4b01HC+cYTZcSlxEcU6BkUni5Zd4pcsrPoPUsndmWv\nlUJ4/hT8qZ/ZEtNatMoIk8/E24Y8OyU5SFwPWNsCgYEAt6gj+xYUSRAJ17rYrlIB\nGq2SEGOljKZ3r6c9qC6bqCF3mZBKkeQl9IaOEjMKHd5Qz6gZ5US5+uY+SnC9mKa9\nbLRA68FRmSjJp6fFqjee08Uqe/npu3wvoEe4MlRiP/Rmt+nWjP//QKsG5rdmMWei\nS+n/A6XAvqUL4jFyKBzZI70CgYEAnnUr51cIpJemoFFDO7t8zN4bjlF23qmFC8rd\nw0Z1xOZFUUmfGLpKbdTlFHomxkSxKiqGDvyCWBNiRtfsXV595vpJ44Opqj9xWEpy\ntehRRrOo9/7cQxQQuqeO1eUz3vafJKJep+K7PqI2aQOS4C4KL6WPPMPIawNPTt5r\nZqoqfrMCgYEAgjEwaamv9Sx4xKb25xiZ/8cuVCFkcBVOlDLQPLo/qejq+of2RTAQ\nawHcyeJE3zze1zDACYulp0C89rpWuGdpdRIpuVJ8jcWztkSTXB5PyPm2YkZxZC/A\nMSEFlH/iZ9A/kN4wIRqfPsAxI4kfOCuu2qt+a+KCO9k7beJEko2zJKc=\n-----END RSA PRIVATE KEY-----";
}
