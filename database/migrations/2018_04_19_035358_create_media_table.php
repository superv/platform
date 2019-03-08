<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('sv_media', function (Blueprint $table, ResourceConfig $resource) {
            $resource->label('Media');

            $table->increments('id');
            $table->morphTo('owner');
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
        Schema::dropIfExists('sv_media');
    }
}
