<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class AddonFooMigration extends Migration
{
    public function up()
    {
        $this->create('foo_table', function (Blueprint $table, ResourceConfig $config) {
            $table->id();
        });
    }
}