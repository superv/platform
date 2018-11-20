<?php

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('auth_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->timestamps();
        });


        Schema::create('auth_assigned_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('owner');
            $table->unsignedInteger('role_id');

            $table->timestamps();
        });

        Schema::create('auth_assigned_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('owner');
            $table->unsignedInteger('action_id');

            $table->select('provision')->options(['pass' => 'Pass', 'fail' => 'Fail']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auth_roles');
    }
}
