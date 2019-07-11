<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class CreateUsersResource extends Migration
{
    public function up()
    {
        Schema::run('users', function (Blueprint $table, ResourceConfig $resource) {
            $resource->resourceKey('user');
            $resource->nav('acp.platform.auth');

            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('remember_token')->nullable();

            $table->hasOne('sv_profiles', 'profile', 'user_id');
            $table->morphToMany('sv_auth_roles', 'roles', 'owner', 'sv_auth_assigned_roles', 'role_id');

            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            };
            $table->morphToMany('sv_auth_actions', 'actions', 'owner', 'sv_auth_assigned_actions', 'action_id', $pivotColumns);
        });

        Schema::table('users', function(Blueprint $table) {
            $table->restorable();
        });
    }

    public function down()
    {
    }
}
