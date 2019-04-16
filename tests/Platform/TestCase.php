<?php

namespace Tests\Platform;

use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Domains\Addon\Locator;
use SuperV\Platform\Testing\PlatformTestCase;

class TestCase extends PlatformTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->app->setBasePath(realpath(__DIR__.'/../../'));
    }

    protected function installAddons(): void
    {
        foreach ($this->installs as $addon) {
            app(Installer::class)
                ->setLocator(new Locator(realpath(__DIR__.'/../../../../')))
                ->setSlug($addon)
                ->install();
        }
    }

    public function basePath($path = null)
    {
        return __DIR__.($path ? '/'.$path : '');
    }
}