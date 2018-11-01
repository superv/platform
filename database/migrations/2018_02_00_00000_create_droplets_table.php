<?php

use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateDropletsTable extends Migration
{
    public function up()
    {
        Schema::create('sv_droplets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('vendor');
            $table->string('slug');
            $table->string('path');
            $table->string('namespace');
            $table->string('type');
            $table->boolean('enabled');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_droplets');
    }
}
