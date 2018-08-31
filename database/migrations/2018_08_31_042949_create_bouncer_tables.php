<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Silber\Bouncer\Database\Models;

class CreateBouncerTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bouncer_abilities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('title')->nullable();
            $table->integer('entity_id')->unsigned()->nullable();
            $table->string('entity_type', 150)->nullable();
            $table->boolean('only_owned')->default(false);
            $table->integer('scope')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('bouncer_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('title')->nullable();
            $table->integer('level')->unsigned()->nullable();
            $table->integer('scope')->nullable()->index();
            $table->timestamps();

            $table->unique(
                ['name', 'scope'],
                'roles_name_unique'
            );
        });

        Schema::create('bouncer_assigned_roles', function (Blueprint $table) {
            $table->integer('role_id')->unsigned()->index();
            $table->integer('entity_id')->unsigned();
            $table->string('entity_type', 150);
            $table->integer('scope')->nullable()->index();

            $table->index(
                ['entity_id', 'entity_type', 'scope'],
                'assigned_roles_entity_index'
            );

            $table->foreign('role_id')
                  ->references('id')->on('bouncer_roles')
                  ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('bouncer_permissions', function (Blueprint $table) {
            $table->integer('ability_id')->unsigned()->index();
            $table->integer('entity_id')->unsigned();
            $table->string('entity_type', 150);
            $table->boolean('forbidden')->default(false);
            $table->integer('scope')->nullable()->index();

            $table->index(
                ['entity_id', 'entity_type', 'scope'],
                'permissions_entity_index'
            );

            $table->foreign('ability_id')
                  ->references('id')->on('bouncer_abilities')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bouncer_permissions');
        Schema::drop('bouncer_assigned_roles');
        Schema::drop('bouncer_roles');
        Schema::drop('bouncer_abilities');
    }
}
