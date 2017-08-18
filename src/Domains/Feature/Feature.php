<?php namespace SuperV\Platform\Domains\Feature;


abstract class Feature
{
    use ServesFeaturesTrait;

    public static $route;

    public static $resolvable = [];

    protected $middlewares;

    /**
     * @var array
     */
    private $params;

    public function __construct(array $params = null)
    {
        $this->params = $params;
    }

    public function param($name, $default = null)
    {
       return array_get($this->params, $name, $default);
    }

    public function __get($name)
    {
       return array_get($this->params, $name);
    }

    /**
     * @return mixed
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }
}