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

            $table->hasOne('platform.profiles', 'profile', 'user_id');

            $table->morphToMany('platform.auth_roles', 'roles', 'owner')
                  ->pivotTable('sv_auth_assigned_roles', 'platform.auth_assigned_roles')
                  ->pivotRelatedKey('role_id');

            $table->morphToMany('platform.auth_actions', 'actions', 'owner')
                  ->pivotTable('sv_auth_assigned_actions', 'platform.auth_assigned_actions')
                  ->pivotRelatedKey('action_id')
                  ->pivotColumns(function (Blueprint $pivotTable) {
                      $pivotTable->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
                  });

            $table->restorable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->nullableBelongsTo('users', 'deleted_by');
        });
    }

    public function down()
    {
    }
}
