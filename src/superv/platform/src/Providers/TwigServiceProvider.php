<?php

namespace SuperV\Platform\Providers;

use TwigBridge\Facade\Twig;
use TwigBridge\ServiceProvider as TwigBridgeServiceProvider;

class TwigServiceProvider extends BaseServiceProvider
{
    protected $providers = [TwigBridgeServiceProvider::class];

    protected $aliases = ['Twig' => Twig::class];
}