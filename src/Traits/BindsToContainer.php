<?php

namespace SuperV\Platform\Traits;

use Illuminate\Foundation\AliasLoader;

trait BindsToContainer
{
    public function registerBindings(array $bindings)
    {
        foreach ($bindings as $abstract => $concrete) {
            app()->bind($abstract, $concrete);
        }
    }

    public function registerProviders($providers)
    {
        foreach ((array)$providers as $provider) {
            app()->register($provider);
        }
    }

    public function registerAliases($aliases)
    {
        if ($aliases && is_array($aliases) && ! empty($aliases)) {
            AliasLoader::getInstance($aliases)->register();
        }
    }

    public function registerSingletons(array $singletons)
    {
        foreach ($singletons as $abstract => $concrete) {
            if (is_numeric($abstract) && is_string($concrete)) {
                //if (str_is('*~*', $concrete)) {
                //    list($concrete, $binding) = explode('~', $concrete);
                //    $this->app->bindIf("superv.{$binding}", $concrete);
                //}
                $abstract = $concrete;
            } else {
                if (! preg_match('/[^A-Za-z._\-]/', $abstract)) {
                    // platform binding
                    app()->singleton("superv.{$abstract}", $concrete);
                    $abstract = $concrete;
                } else {
                    app()->singleton($abstract, $concrete);
                }
            }

            app()->singleton($abstract, $concrete);
        }
    }
}
