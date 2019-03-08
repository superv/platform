<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;

class CreateAuthorizationTables extends Migration
{
    public function up()
    {
        Schema::create('sv_auth_roles', function (Blueprint $table) {
            $table->resourceConfig()->label('Roles');
            $table->resourceConfig()->nav('acp.platform.auth');

            $table->increments('id');
            $table->string('slug')->unique();
            $table->createdBy()->updatedBy();
            $table->restorable();

            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            };
            $table->morphToMany('sv_auth_actions', 'actions', 'owner', 'sv_auth_assigned_actions', 'action_id', $pivotColumns);
        });

        Schema::create('sv_auth_assigned_roles', function (Blueprint $table) {
            $table->resourceConfig()->label('Assigned Roles');

            $table->increments('id');
            $table->morphTo('owner');
            $table->unsignedInteger('role_id');

            $table->createdBy()->updatedBy();
        });

        Schema::create('sv_auth_actions', function (Blueprint $table) {
            $table->resourceConfig()->label('Actions');
            $table->resourceConfig()->nav('acp.platform.auth');

            $table->increments('id');
            $table->string('slug')->unique();
            $table->createdBy()->updatedBy();
            $table->restorable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_auth_roles');
        Schema::dropIfExists('sv_auth_assigned_roles');
        Schema::dropIfExists('sv_auth_actions');
        Schema::dropIfExists('sv_auth_assigned_actions');
    }
}
