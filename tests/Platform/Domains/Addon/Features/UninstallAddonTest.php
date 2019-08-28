<?php

namespace Tests\Platform\Domains\Addon\Features;

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

    function test__rollback_migrations()
    {
        $this->setUpAddon(null, null);
        $this->assertEquals(3, \DB::table('migrations')->where('namespace', 'superv.addons.sample')->count());

        UninstallAddonJob::dispatch('superv.addons.sample');

        $this->assertEquals(0, \DB::table('migrations')->where('namespace', 'superv.addons.sample')->count());
    }
}
