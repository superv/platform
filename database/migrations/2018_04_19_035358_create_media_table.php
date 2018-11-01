<?php

use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('owner');
            $table->string('disk');
            $table->string('original');
            $table->string('filename');
            $table->string('mime_type');
            $table->string('label');
            $table->string('extension');
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
}
