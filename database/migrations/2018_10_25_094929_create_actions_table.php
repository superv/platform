<?php

use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

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
