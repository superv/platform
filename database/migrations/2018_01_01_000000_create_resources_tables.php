<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateResourcesTables extends Migration
{

    public function up()
    {
//        /**
//         * First create the tables with framework's Schema
//         */
//        foreach (PlatformBlueprints::$resources as $resource => $table) {
//            LaravelSchema::create($table,
//                function (LaravelBlueprint $table) use ($resource) {
//                    PlatformBlueprints::{$resource}($table);
//                }
//            );
//        }

        /**
         * Then run the migrations again to create the resources
         */
//        foreach (PlatformBlueprints::$resources as $resource => $table) {
//            Schema::run($table,
//                function (Blueprint $table, Config $config) use ($resource) {
//                    $config->setName($resource);
//                    PlatformBlueprints::{$resource}($table, $config);
//                }
//            );
//        }
    }

    public function down()
    {
//        foreach (PlatformBlueprints::$resources as $resource => $table) {
//            Schema::dropIfExists($table);
//        }
    }
}
