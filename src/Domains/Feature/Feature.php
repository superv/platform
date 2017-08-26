<?php namespace SuperV\Platform\Domains\Feature;


abstract class Feature
{
    use ServesFeaturesTrait;

    public static $route;

    public static $resolvable = [];

    protected $middlewares;


    /**
     * @return mixed
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }
}