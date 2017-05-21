<?php namespace SuperV\Platform\Domains\Feature;

use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Feature
{
    use MarshalTrait;
    use JobDispatcherTrait;

    public static $route;

    protected $resolves = [];

    /** @var  \SuperV\Platform\Support\Collection */
    protected $request;

    public function request($request)
    {
        $this->request = $request;

        return $this;
    }

    public function resolves()
    {
        return $this->resolves;
    }
}