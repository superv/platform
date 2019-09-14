<?php

use SuperV\Platform\Domains\Auth\Access\Role;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class CreateAuthorizationTables extends Migration
{
    public function up()
    {
        Section::createFromArray([
            'parent' => 'acp.platform',
            'title'  => 'Auth',
            'handle' => 'auth',
            'icon'   => 'auth',
        ]);
        Schema::create('sv_auth_roles', function (Blueprint $table, ResourceConfig $resource) {
            $resource->label('Roles');
            $resource->model(Role::class);
            $resource->nav('acp.platform.auth');

            $table->increments('id');
            $table->string('slug')->unique()->entryLabel();
            $table->createdBy()->updatedBy();
            $table->restorable();

            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            };
            $table->morphToMany('platform::sv_auth_actions', 'actions', 'owner', 'sv_auth_assigned_actions', 'action_id', $pivotColumns);
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
            $table->string('slug')->unique()->entryLabel();
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
