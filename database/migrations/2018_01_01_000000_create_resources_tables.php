<?php

use Illuminate\Database\Schema\Blueprint as LaravelBlueprint;
use Schema as LaravelSchema;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class CreateResourcesTables extends Migration
{
    public function up()
    {
        LaravelSchema::create('sv_namespaces', function (LaravelBlueprint $table) {
            Blueprints::namespaces($table);
        });
        LaravelSchema::create('sv_resources', function (LaravelBlueprint $table) {
            Blueprints::resources($table);
        });
        LaravelSchema::create('sv_fields', function (LaravelBlueprint $table) {
            Blueprints::fields($table);
        });
        LaravelSchema::create('sv_forms', function (LaravelBlueprint $table) {
            Blueprints::forms($table);
        });
        LaravelSchema::create('sv_relations', function (LaravelBlueprint $table) {
            Blueprints::relations($table);
        });
        LaravelSchema::create('sv_navigation', function (LaravelBlueprint $table) {
            Blueprints::navigation($table);
        });
        LaravelSchema::create('sv_activities', function (LaravelBlueprint $table) {
            Blueprints::activity($table);
        });

        $this->selfAware();
    }

    protected function selfAware()
    {
        Section::createFromArray([
            'parent' => 'acp.platform',
            'title'  => 'System',
            'handle' => 'system',
            'icon'   => 'system',
        ]);

        Schema::run('sv_namespaces', function (Blueprint $table, ResourceConfig $resource) {
            Blueprints::namespaces($table, $resource);
        });
        Schema::run('sv_resources', function (Blueprint $table, ResourceConfig $resource) {
            Blueprints::resources($table, $resource);
        });
        Schema::run('sv_fields', function (Blueprint $table, ResourceConfig $resource) {
            Blueprints::fields($table, $resource);
        });
        Schema::run('sv_forms', function (Blueprint $table, ResourceConfig $resource) {
            Blueprints::forms($table, $resource);
        });
        Schema::run('sv_relations', function (Blueprint $table, ResourceConfig $resource) {
            Blueprints::relations($table, $resource);
        });
        Schema::run('sv_navigation', function (Blueprint $table, ResourceConfig $resource) {
            Blueprints::navigation($table, $resource);
        });
        Schema::run('sv_activities', function (Blueprint $table, ResourceConfig $resource) {
            Blueprints::activity($table, $resource);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_namespaces');
        Schema::dropIfExists('sv_resources');
        Schema::dropIfExists('sv_fields');
        Schema::dropIfExists('sv_forms');
        Schema::dropIfExists('sv_form_fields');
        Schema::dropIfExists('sv_relations');
        Schema::dropIfExists('sv_navigation');
        Schema::dropIfExists('sv_activities');
    }
}
