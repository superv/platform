<?php

namespace Tests\Platform;

use Current;
use Hub;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Domains\Addon\Locator;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\PlatformServiceProvider;
use SuperV\Platform\Testing\PlatformTestCase;
use SuperV\Platform\Testing\TestHelpers;

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