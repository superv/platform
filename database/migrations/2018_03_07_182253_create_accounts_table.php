<?php

use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('sv_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_accounts');
    }
}
