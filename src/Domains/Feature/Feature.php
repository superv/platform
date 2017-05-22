<?php namespace SuperV\Platform\Domains\Feature;


abstract class Feature
{
    use MarshalTrait;
    use JobDispatcherTrait;

    public static $route;

    public static $resolvable = [];

    protected $params;

    public function params($params)
    {
        $this->params = $params;

        return $this;
    }

    public function resolves()
    {
        return $this->resolves;
    }

    public function __get($name)
    {
       return array_get($this->params, $name);
    }
}