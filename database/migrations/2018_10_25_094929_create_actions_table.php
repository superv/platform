<?php

use SuperV\Platform\Domains\Database\Blueprint\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema;

class CreateActionsTable extends Migration
{
    public function up()
    {
        Schema::create('auth_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auth_actions');
    }
}
