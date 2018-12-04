<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('auth_roles', function (Blueprint $table) {
            $table->resourceBlueprint()->label('Roles');

            $table->increments('id');
            $table->string('slug')->unique();
            $table->createdBy()->updatedBy();
            $table->restorable();
        });

        Schema::create('auth_assigned_roles', function (Blueprint $table) {
            $table->resourceBlueprint()->label('Assigned Roles');
            $table->increments('id');
            $table->morphs('owner');
            $table->unsignedInteger('role_id');

            $table->createdBy()->updatedBy();
        });

        Schema::create('auth_actions', function (Blueprint $table) {
            $table->resourceBlueprint()->label('Actions');

            $table->increments('id');
            $table->string('slug')->unique();
            $table->createdBy()->updatedBy();
            $table->restorable();
        });

        Schema::create('auth_assigned_actions', function (Blueprint $table) {
            $table->resourceBlueprint()->label('Assigned Actions');
            $table->increments('id');
            $table->morphs('owner');
            $table->unsignedInteger('action_id');

            $table->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            $table->createdBy()->updatedBy();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auth_roles');
    }
}
