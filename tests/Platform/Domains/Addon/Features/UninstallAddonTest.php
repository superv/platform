<?php

namespace Tests\Platform\Platform\Domains\Addon\Features;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Addon\Jobs\UninstallAddonJob;
use Tests\Platform\TestCase;

class UninstallAddonTest extends TestCase
{
    use RefreshDatabase;

    function test__uninstall_successfully()
    {
        $this->setUpAddon(null, null, $seed = true);

        UninstallAddonJob::dispatch('superv.addons.sample');

        $this->assertDatabaseMissing('sv_addons', ['slug' => 'superv.addons.sample']);
    }
}