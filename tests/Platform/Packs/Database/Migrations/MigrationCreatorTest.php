<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations;

use Platform;
use SuperV\Platform\Packs\Database\Migrations\MigrationCreator;
use Tests\SuperV\Platform\BaseTestCase;

class MigrationCreatorTest extends BaseTestCase
{
    /**
     * @test
     */
    function extends_framework_creator()
    {
        $this->assertInstanceOf('Illuminate\Database\Migrations\MigrationCreator', $this->creator());
    }

    /**
     * @test
     */
    function modifies_stubs_location()
    {
        $this->assertEquals(Platform::fullPath('resources/stubs'), $this->creator()->stubPath());
    }

    /**
     * @test
     */
    function adds_scope_data_if_supplied()
    {
        $file = $this->creator()
                     ->setScope('blank')
                     ->create('Create', storage_path('testing'));
        $this->assertContains("\$scope = 'blank'", file_get_contents($file));

        $file = $this->creator()
                     ->setScope('create')
                     ->create('Create', storage_path('testing'), 'FooTable', $create = true);
        $this->assertContains("\$scope = 'create'", file_get_contents($file));

        $file = $this->creator()
                     ->setScope('update')
                     ->create('Update', storage_path('testing'), 'FooTable');
        $this->assertContains("\$scope = 'update'", file_get_contents($file));
    }

    /**
     * @return \SuperV\Platform\Packs\Database\Migrations\MigrationCreator
     */
    protected function creator()
    {
        return app(MigrationCreator::class);
    }

    protected function setUp()
    {
        parent::setUp();

        if (! file_exists(storage_path('testing'))) {
            app('files')->makeDirectory(storage_path('testing'));
        }
    }

    protected function tearDown()
    {
        app('files')->deleteDirectory(storage_path('testing'));

        parent::tearDown();
    }
}