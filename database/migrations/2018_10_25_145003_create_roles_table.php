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
            $table->resourceBlueprint()->nav('acp.platform.auth');

            $table->increments('id');
            $table->string('slug')->unique();
            $table->createdBy()->updatedBy();
            $table->restorable();

            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            };
            $table->morphToMany('auth_actions', 'actions', 'owner', 'auth_assigned_actions', 'action_id', $pivotColumns);
        });

        Schema::create('auth_assigned_roles', function (Blueprint $table) {
            $table->resourceBlueprint()->label('Assigned Roles');

            $table->increments('id');
            $table->morphTo('owner');
            $table->unsignedInteger('role_id');

            $table->createdBy()->updatedBy();
        });

        Schema::create('auth_actions', function (Blueprint $table) {
            $table->resourceBlueprint()->label('Actions');
            $table->resourceBlueprint()->nav('acp.platform.auth');

            $table->increments('id');
            $table->string('slug')->unique();
            $table->createdBy()->updatedBy();
            $table->restorable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auth_roles');
        Schema::dropIfExists('auth_assigned_roles');
        Schema::dropIfExists('auth_actions');
        Schema::dropIfExists('auth_assigned_actions');
    }
}
