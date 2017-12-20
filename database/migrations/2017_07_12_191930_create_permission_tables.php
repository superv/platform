<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionTables extends Migration
{
    public function up()
    {
        Schema::create('auth_perms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('auth_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('auth_model_has_perms', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->morphs('model');

            $table->foreign('permission_id')
                ->references('id')
                ->on('auth_perms')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('auth_model_has_roles', function (Blueprint $table)  {
            $table->integer('role_id')->unsigned();
            $table->morphs('model');

            $table->foreign('role_id')
                ->references('id')
                ->on('auth_roles')
                ->onDelete('cascade');

            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('auth_role_has_perms', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->foreign('permission_id')
                ->references('id')
                ->on('auth_perms')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('auth_roles')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('auth_role_has_perms');
        Schema::drop('auth_model_has_roles');
        Schema::drop('auth_model_has_perms');
        Schema::drop('auth_roles');
        Schema::drop('auth_perms');
    }
}
