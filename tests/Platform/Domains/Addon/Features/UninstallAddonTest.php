<?php

namespace Tests\Platform\Domains\Addon\Features;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Addon\Jobs\UninstallAddonJob;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\TestCase;

class UninstallAddonTest extends TestCase
{
    use RefreshDatabase;

    function test__uninstall_successfully()
    {
        $this->setUpAndSeedAddon('superv.sample');

        UninstallAddonJob::dispatch('sample');

        $this->assertDatabaseMissing('sv_addons', ['identifier' => 'sample']);
    }

    function test__rollback_migrations()
    {
        $this->setUpAddon(null, null);
        $this->assertEquals(3, \DB::table('migrations')->where('namespace', 'sample')->count());

        UninstallAddonJob::dispatch('sample');

        $this->assertEquals(0, \DB::table('migrations')->where('namespace', 'sample')->count());
    }

    function test__deletes_resources()
    {
        $this->setUpAddon(null, null);

        $this->assertEquals(1, ResourceModel::query()->where('namespace', 'sample')->count());

        UninstallAddonJob::dispatch('sample');

        $this->assertEquals(0, ResourceModel::query()->where('namespace', 'sample')->count());
    }
}
