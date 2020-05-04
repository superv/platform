<?php

namespace SuperV\Platform\Domains\Addon\Contracts;

interface AddonInterface
{
    public function realPath($prefix = null);

    public function path($prefix = null);

    public function getHandle();

    public function getIdentifier();

    public function getPsrNamespace();

    public function loadConfigFiles();

    public function resourcePath($prefix = null);

    public function getVendor();

    public function getType();

    public function boot();
}