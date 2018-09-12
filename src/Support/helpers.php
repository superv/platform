<?php

use Illuminate\Container\Container;
use SuperV\Modules\Nucleo\Domains\UI\SvBlock;
use SuperV\Modules\Nucleo\Domains\UI\SvTab;
use SuperV\Modules\Nucleo\Domains\UI\SvTabs;
use SuperV\Platform\Domains\Authorization\Guardable;
use SuperV\Platform\Domains\Authorization\Haydar;
use SuperV\Platform\Domains\Feature\FeatureBus;
use SuperV\Platform\Support\Collection;
use SuperV\Platform\Support\Composer\Composer;
use SuperV\Platform\Support\Decorator;
use SuperV\Platform\Support\RelativePath;

function platform_path($path = null)
{
    return 'vendor/superv/platform'.(is_null($path) ? '' : DIRECTORY_SEPARATOR.$path);
}

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
    if (! $guardable instanceof Guardable && ! is_array($guardable) && ! $guardable instanceof \Illuminate\Support\Collection) {
        return $guardable;
    }

    return app(Haydar::class)->guard($guardable);
}

/**
 * Create a collection from the given value.
 *
 * @param  mixed $value
 * @return \SuperV\Platform\Support\Collection
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

function sv_basename($path)
{
    return pathinfo($path, PATHINFO_BASENAME);
}

function sv_filename($path)
{
    return pathinfo($path, PATHINFO_FILENAME);
}

function uuid()
{
    $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();

    return str_replace('-', '', $uuid);
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
    return SvBlock::make()->url($url);
}
