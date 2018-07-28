<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreatePrototypesTable extends Migration
{
    public function up()
    {
        Schema::create('nucleo_prototypes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('table');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('nucleo_prototypes');
    }
}
