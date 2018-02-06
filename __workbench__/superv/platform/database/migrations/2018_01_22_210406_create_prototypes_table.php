<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Packs\Database\Migrations\Migration;

class CreatePrototypesTable extends Migration
{
    public function up()
    {
        Schema::create('prototypes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('table');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('prototypes');
    }
}
