<?php

namespace SuperV\Platform\Contracts;

abstract class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    abstract public function register();
}
