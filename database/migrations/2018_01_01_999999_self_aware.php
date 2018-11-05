<?php

use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class SelfAware extends Migration
{
    public function up()
    {
        Schema::nucleo('sv_resources', function (Blueprint $table) {
            Blueprints::resources($table);
        });

        Schema::nucleo('sv_resource_fields', function (Blueprint $table) {
            Blueprints::fields($table);
        });

        Schema::nucleo('sv_relations', function (Blueprint $table) {
            Blueprints::relations($table);
        });
    }

    public function down()
    {

    }
}
