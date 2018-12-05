<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->morphTo('owner')->showOnIndex();
            $table->string('disk')->showOnIndex();
            $table->string('original')->showOnIndex();
            $table->string('filename');
            $table->string('mime_type');
            $table->string('label')->showOnIndex();
            $table->string('extension');
            $table->unsignedInteger('size');

            $table->createdBy()->updatedBy();
            $table->restorable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
}
