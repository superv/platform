<?php

use Illuminate\Database\Schema\Blueprint as LaravelBlueprint;
use Schema as LaravelSchema;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use SuperV\Platform\Domains\Resource\Support\PlatformBlueprints;

class CreateResourcesTables extends Migration
{
    protected $resources = [
        'namespaces' => 'sv_namespaces',
        'resources'  => 'sv_resources',
        'fields'     => 'sv_fields',
        'forms'      => 'sv_forms',
        'relations'  => 'sv_relations',
        'navigation' => 'sv_navigation',
        'activities' => 'sv_activities',
    ];

    public function up()
    {
        /**
         * First create the tables with framework's Schema
         */
        foreach ($this->resources as $resource => $table) {
            LaravelSchema::create($table,
                function (LaravelBlueprint $table) use ($resource) {
                    PlatformBlueprints::{$resource}($table);
                }
            );
        }

        /**
         * Then run the migrations again to create the resources
         */
        foreach ($this->resources as $resource => $table) {
            Schema::run($table,
                function (Blueprint $table, Config $config) use ($resource) {
                    $config->setName($resource);
                    PlatformBlueprints::{$resource}($table, $config);
                }
            );
        }
    }

    public function down()
    {
        foreach ($this->resources as $resource => $table) {
            Schema::dropIfExists($table);
        }
    }
}
