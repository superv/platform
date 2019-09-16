<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;

class CreateUsersResource extends Migration
{
    public function up()
    {
        Schema::run('users', function (Blueprint $table, Config $config) {
            $config->resourceKey('user');
            $config->nav('acp.platform.auth');
            $config->model(config('superv.auth.user.model'));

            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('remember_token')->nullable();

            $table->hasOne('sv_profiles', 'profile', 'user_id');
            $table->morphToMany('platform::sv_auth_roles', 'roles', 'owner', 'sv_auth_assigned_roles', 'role_id');

            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            };
            $table->morphToMany('platform::sv_auth_actions', 'actions', 'owner', 'sv_auth_assigned_actions', 'action_id', $pivotColumns);

            $table->restorable();
        });

        Schema::table('users', function(Blueprint $table) {
            $table->softDeletes();
            $table->nullableBelongsTo('users', 'deleted_by');
        });
    }

    public function down()
    {
    }
}
