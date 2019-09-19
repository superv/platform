<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;

class CreateProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('sv_profiles', function (Blueprint $table, Config $config) {
            $config->label('User Profiles');
            $config->setName('profiles');

            $table->increments('id');
            $table->belongsTo('users', 'user');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->file('avatar', 'sv/users/avatar');
            $table->createdBy()->updatedBy();
            $table->restorable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_profiles');
    }
}
