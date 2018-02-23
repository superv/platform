<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateStructsTable extends Migration
{
    public function up()
    {
        Schema::create('structs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('prototype_id');
            $table->unsignedInteger('related_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('structs');
    }
}
