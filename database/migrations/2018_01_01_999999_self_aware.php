<?php

use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class SelfAware extends Migration
{
    public function up()
    {
        Schema::nucleo('sv_resources', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::resources($table, $resource);
        });

        Schema::nucleo('sv_fields', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::fields($table, $resource);
        });

        Schema::nucleo('sv_relations', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::relations($table, $resource);
        });

        Schema::nucleo('sv_navigation', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::navigation($table, $resource);
        });
    }

    public function down()
    {

    }
}
