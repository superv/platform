<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations;

use Platform;
use SuperV\Platform\Packs\Database\Migrations\MigrationCreator;
use Tests\SuperV\Platform\BaseTestCase;

class MigrationCreatorTest extends BaseTestCase
{
    protected $tmpDirectory = 'testing-migrations';

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
                     ->create('Create', storage_path($this->tmpDirectory));
        $this->assertContains("\$scope = 'blank'", file_get_contents($file));

        $file = $this->creator()
                     ->setScope('create')
                     ->create('Create', storage_path($this->tmpDirectory), 'FooTable', $create = true);
        $this->assertContains("\$scope = 'create'", file_get_contents($file));

        $file = $this->creator()
                     ->setScope('update')
                     ->create('Update', storage_path($this->tmpDirectory), 'FooTable');
        $this->assertContains("\$scope = 'update'", file_get_contents($file));
    }

    /**
     * @return \SuperV\Platform\Packs\Database\Migrations\MigrationCreator
     */
    protected function creator()
    {
        return app(MigrationCreator::class);
    }
}