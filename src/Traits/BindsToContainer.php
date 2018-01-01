<?php

namespace SuperV\Platform\Traits;

use Illuminate\Foundation\AliasLoader;

trait BindsToContainer
{
    public function registerBindings(array $bindings)
    {
        collect($bindings)
            ->map(function ($concrete, $abstract) {
                app()->bind($abstract, $concrete);
            });
    }

    public function registerProviders($providers)
    {
        collect($providers)
            ->map(function ($provider) {
                app()->register($provider);
            });
    }

    public function registerAliases($aliases)
    {
        if ($aliases && is_array($aliases) && ! empty($aliases)) {
            AliasLoader::getInstance($aliases)->register();
        }
    }

    public function registerSingletons(array $singletons)
    {
        collect($singletons)
            ->map(function ($concrete, $abstract) {
                if (is_numeric($abstract) && is_string($concrete)) {
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
            });
    }
}
