<?php

namespace Tests\SuperV\Platform\Domains\Database\Migrations;

use Illuminate\Database\Migrations\Migrator as BaseMigrator;
use SuperV\Platform\Domains\Database\Migrations\Migrator;
use Tests\SuperV\Platform\BaseTestCase;

class MigratorTest extends BaseTestCase
{
    /**
     * @test
     */
    function extends_framework_migrator()
    {
        $migrator = new Migrator(app('migration.repository'), app('db'), app('files'));

        $this->assertInstanceOf(BaseMigrator::class, $migrator);
    }
}