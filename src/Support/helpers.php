<?php

use Illuminate\Container\Container;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Routing\UrlGenerator;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Nucleo\SvBlock;
use SuperV\Platform\Domains\UI\Nucleo\SvComponent;
use SuperV\Platform\Domains\UI\Nucleo\SvTab;
use SuperV\Platform\Domains\UI\Nucleo\SvTabs;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Composer\Composer;
use SuperV\Platform\Support\Identifier;
use SuperV\Platform\Support\Parser;
use SuperV\Platform\Support\RelativePath;

function ddh()
{
    dd('Over here! '.date('H:i:s'), func_get_args());
}

function sv_debug()
{
    PlatformException::debug(...func_get_args());
}

function get_ns_from_file($file)
{
    $fp = fopen($file, 'r');

    $namespace = $buffer = '';
    while (! $namespace) {
        if (feof($fp)) {
            break;
        }

        $buffer .= fgets($fp, 512);
        $re = '/namespace\s+(((\w+\\\\)+)(\w+)\s*);/';
        if (preg_match($re, $buffer, $matches)) {
            $namespace = $matches[1];
            break;
        }
    }

    return $namespace;
}

function get_json($path, $filename = null)
{
    if ($filename) {
        $path .= DIRECTORY_SEPARATOR.$filename.'.json';
    }

    if (! file_exists($path)) {
        throw new Exception("JSON file does not exist at path: ".$path);
    }

    $jsonData = json_decode(file_get_contents($path), true);

    return $jsonData;
}

function sv_trans($key = null, $replace = [], $locale = null)
{
    $line = trans($key, $replace, $locale);

    if ($line !== $key) {
        return $line;
    }

    return __($line);
}

function dump_callers($limit = 10)
{
    $callers = get_callers($limit);

    $callers->map(function ($caller) { dump($caller); });
}

function ddq(Builder $query)
{
    dd($query->toSql(), $query->getBindings());
}

function get_callers($limit = 10): Collection
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
    return array_filter($array, function ($item) {
        if (is_null($item)) {
            return false;
        }

        if (is_array($item) && count($item) === 0) {
            return false;
        }

        return true;
    });
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
     * @param string $abstract
     * @param array  $parameters
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

function sv_identifier($string)
{
    return Identifier::make($string);
}

function sv_console()
{
    if (config('app.debug')) {
        Log::debug('console', func_get_args());
    }
}

/**
 * @param null $addon
 * @return \SuperV\Platform\Domains\Addon\Addon|\SuperV\Platform\Domains\Addon\AddonCollection
 */
function sv_addons($addon = null)
{
    if (is_null($addon)) {
        return superv('addons');
    }

    return superv('addons')->get($addon);
}

function sv_resource($handle)
{
    return ResourceFactory::make($handle);
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
 * @param mixed $value
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

function sv_compose($data, $tokens = null)
{
    return (new Composer($tokens))->compose($data);
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

function sv_route($name, $parameters = [])
{
    return sv_url(route($name, $parameters, false));
}

function uuid()
{
    $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();

    return str_replace('-', '', $uuid);
}

function str_unslug(string $slug)
{
    return ucwords(str_replace(['_', '.'], ' ', $slug));
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

function sv_loader($url, array $props = [])
{
    if (! starts_with($url, 'http')) {
//        $url = sv_url($url);
    }

    if ($qs = request()->getQueryString()) {
        $url .= '?'.$qs;
    }

    $props['url'] = $url;

    return Component::make('sv-loader')->setProps($props);
}

function sv_field(array $params)
{
    return FormField::make($params);
}

function wrap_collect($obj)
{
    if ($obj instanceof Collection) {
        return $obj;
    }

    return collect($obj);
}

function wrap_array($obj)
{
    if (is_array($obj)) {
        return $obj;
    }

    if (is_null($obj)) {
        return [];
    }

    return [$obj];
}

if (! function_exists('studly_case')) {
    function studly_case($value)
    {
        return Str::studly($value);
    }
}

if (! function_exists('camel_case')) {
    function camel_case($value)
    {
        return Str::camel($value);
    }
}

if (! function_exists('snake_case')) {
    function snake_case($value)
    {
        return Str::snake($value);
    }
}

if (! function_exists('str_contains')) {
    function str_contains($haystack, $needles)
    {
        return Str::contains($haystack, $needles);
    }
}

if (! function_exists('starts_with')) {
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}

if (! function_exists('ends_with')) {
    function ends_with($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }
}

if (! function_exists('str_slug')) {
    function str_slug($title, $separator = '-', $language = 'en')
    {
        return Str::slug($title, $separator, $language);
    }
}
if (! function_exists('str_singular')) {
    function str_singular($value)
    {
        return Str::singular($value);
    }
}
if (! function_exists('str_plural')) {
    function str_plural($value)
    {
        return Str::plural($value);
    }
}
if (! function_exists('str_random')) {
    function str_random($length = 12)
    {
        return Str::random($length);
    }
}

if (! function_exists('str_replace_last')) {
    function str_replace_last($search, $replace, $subject)
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}

if (! function_exists('str_replace_first')) {
    function str_replace_first($search, $replace, $subject)
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}

if (! function_exists('str_is')) {
    function str_is($pattern, $value)
    {
        return Str::is($pattern, $value);
    }
}

if (! function_exists('array_first')) {
    function array_first($array, callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}
if (! function_exists('array_only')) {
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}

if (! function_exists('array_random')) {
    function array_random($array, $number = null)
    {
        return Arr::random($array, $number);
    }
}

if (! function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (! function_exists('array_set')) {
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}

if (! function_exists('array_flatten')) {
    function array_flatten($array, $depth = INF)
    {
        return Arr::flatten($array, $depth);
    }
}
if (! function_exists('array_except')) {
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}

if (! function_exists('array_pull')) {
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}
