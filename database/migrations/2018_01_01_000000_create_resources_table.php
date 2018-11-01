<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateResourcesTable extends Migration
{
    public function up()
    {
        Schema::create('platform_resources', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique();
//            $table->unsignedInteger('droplet_id');
            $table->unsignedInteger('title_field_id')->nullable();
            $table->string('slug');
            $table->string('droplet_slug');
            $table->string('model')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('platform_resources');
    }
}
