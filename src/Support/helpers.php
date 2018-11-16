<?php

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use SuperV\Modules\Nucleo\Domains\UI\SvBlock;
use SuperV\Modules\Nucleo\Domains\UI\SvComponent;
use SuperV\Modules\Nucleo\Domains\UI\SvTab;
use SuperV\Modules\Nucleo\Domains\UI\SvTabs;
use SuperV\Platform\Domains\Feature\FeatureBus;
use SuperV\Platform\Domains\Routing\UrlGenerator;
use SuperV\Platform\Support\Composer\Composer;
use SuperV\Platform\Support\Parser;
use SuperV\Platform\Support\RelativePath;

/**
 * @param null  $handler
 * @param array $input
 * @return FeatureBus
 */
function feature($handler = null, array $input = [])
{
    if ($handler) {
        return \Feature::handler($handler)->merge($input);
    }

    return app(FeatureBus::class);
}

function dump_callers($limit = 10)
{
    $callers = get_callers($limit);

    $callers->map(function ($caller) { dump($caller); });
}

function get_callers($limit = 10): \Illuminate\Support\Collection
{
    $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit);
    $callers = collect($stack)->map(function ($trace, $key) {
        if ($key < 3) {
            return null;
        }
        $function = $trace['function'] ?? '';

        if (in_array($function, ['get_callers', 'dump_callers', 'array_map'])) {
            return null;
        }

        if (! $class = $trace['class'] ?? '') {
            return $function;
        }
        if (str_contains($class, 'Illuminate\Support')) {
            return null;
        }

        return "[{$key}]".$function.'@'.$class;
    })->filter()->first();

    return collect([$callers]);
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

function array_filter_null(array $array = [])
{
    return array_filter($array, function ($item) { return ! is_null($item); });
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
    if (is_numeric($key)) {
        return $value;
    }

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

/**
 * @param      $key
 * @param null $default
 * @return \Illuminate\Config\Repository|mixed
 */
function sv_config($key, $default = null)
{
    return config("superv.{$key}", $default);
}

/**
 * @param $guardable
 * @return Collection
 */
function sv_guard($guardable)
{
    return (new SuperV\Platform\Domains\Auth\Access\Guard\Guard(Current::user()))->filter($guardable);
}

/**
 * Create a collection from the given value.
 *
 * @param  mixed $value
 * @return \Illuminate\Support\Collection
 */
function sv_collect($value = null)
{
    return new Collection($value);
}

/**
 * Ensure the given path is real
 *
 * @param $path
 */
function sv_real_path($path)
{
    return starts_with($path, '/') ? $path : base_path($path);
}

/**
 * Ensure the given path is real
 *
 * @param $path
 */
function sv_relative_path($path)
{
    return (new RelativePath(base_path()))->get($path);
}

function sv_compose($data)
{
    return (new Composer())->compose($data);
}

function sv_parse($target, array $data)
{
    return app(Parser::class)->parse($target, $data);
}

function sv_basename($path)
{
    return pathinfo($path, PATHINFO_BASENAME);
}

function sv_filename($path)
{
    return pathinfo($path, PATHINFO_FILENAME);
}

function sv_url($path = null)
{
    $generator = app(UrlGenerator::class);
    if (is_null($path)) {
        return $generator;
    }

    return $generator->to($path);
}

function uuid()
{
    $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();

    return str_replace('-', '', $uuid);
}

function str_unslug(string $slug)
{
    return ucwords(str_replace('_', ' ', $slug));
}

function str_prefix(?string $str, $prefix, $glue = '.')
{
    return is_null($str) ? $str : "{$prefix}{$glue}{$str}";
}

/** @return SvTabs */
function sv_tabs()
{
    return SvTabs::make();
}

/**
 * @param $title
 * @param $block
 * @return SvTab
 */
function sv_tab($title, $block)
{
    return (new SvTab)->title($title)->content($block);
}

function sv_block($url = null)
{
    if ($url instanceof SvComponent) {
        return SvBlock::make()->block($url);
    }

    return SvBlock::make()->url($url);
}
