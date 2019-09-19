<?php

use SuperV\Platform\Domains\Auth\Access\Action;
use SuperV\Platform\Domains\Auth\Access\Role;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;

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

        $this->create('sv_auth_roles', function (Blueprint $table, Config $config) {
            $config->label('Roles');
            $config->setName('auth_roles');
            $config->model(Role::class);
            $config->nav('acp.platform.auth');

            $table->increments('id');
            $table->string('slug')->unique()->entryLabel();
            $table->createdBy()->updatedBy();
            $table->restorable();

            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            };

            $table->morphToMany('platform.auth_actions', 'actions', 'owner')
                  ->pivotTable('sv_auth_assigned_actions', 'platform.auth_assigned_actions')
                  ->pivotRelatedKey('action_id')
                  ->pivotColumns($pivotColumns);
        });

        $this->create('sv_auth_assigned_roles', function (Blueprint $table, Config $config) {
            $config->label('Assigned Roles');

            $table->increments('id');
            $table->morphTo('owner');
            $table->unsignedInteger('role_id');

            $table->createdBy()->updatedBy();
        });

        $this->create('sv_auth_actions', function (Blueprint $table, Config $config) {
            $config->label('Actions');
            $config->setName('auth_actions');
            $config->model(Action::class);
            $config->nav('acp.platform.auth');


            $table->increments('id');
            $table->string('slug')->unique()->entryLabel()->searchable();
            $table->string('namespace')->nullable()->addFlag('filter');
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
