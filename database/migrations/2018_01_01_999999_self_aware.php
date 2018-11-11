<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class SelfAware extends Migration
{
    public function up()
    {
        Schema::run('sv_resources', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::resources($table, $resource);
        });

        Schema::run('sv_fields', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::fields($table, $resource);
        });

        Schema::run('sv_relations', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::relations($table, $resource);
        });

        Schema::run('sv_navigation', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::navigation($table, $resource);
        });

        Schema::run('sv_meta', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::meta($table, $resource);
        });
    }

    public function down()
    {
    }
}
