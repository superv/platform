<?php

use Illuminate\Database\Schema\Blueprint as LaravelBlueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Support\Blueprints;
use Schema as LaravelSchema;

class CreateResourcesTables extends Migration
{
    public function up()
    {
        LaravelSchema::create('sv_resources', function (LaravelBlueprint $table) {
            Blueprints::resources($table);
        });
        LaravelSchema::create('sv_fields', function (LaravelBlueprint $table) {
            Blueprints::fields($table);
        });
        LaravelSchema::create('sv_relations', function (LaravelBlueprint $table) {
            Blueprints::relations($table);
        });
        LaravelSchema::create('sv_navigation', function (LaravelBlueprint $table) {
            Blueprints::navigation($table);
        });
        LaravelSchema::create('sv_meta', function (LaravelBlueprint $table) {
            Blueprints::meta($table);
        });
        LaravelSchema::create('sv_meta_items', function (LaravelBlueprint $table) {
            Blueprints::metaItems($table);
        });

        $this->selfAware();
    }

    protected function selfAware()
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
        Schema::run('sv_meta_items', function (Blueprint $table, ResourceBlueprint $resource) {
            Blueprints::metaItems($table, $resource);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_resources');
        Schema::dropIfExists('sv_fields');
        Schema::dropIfExists('sv_relations');
        Schema::dropIfExists('sv_navigation');
        Schema::dropIfExists('sv_meta');
        Schema::dropIfExists('sv_meta_items');
    }
}
