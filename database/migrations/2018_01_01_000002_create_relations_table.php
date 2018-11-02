<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateRelationsTable extends Migration
{
    public function up()
    {
        Schema::create('sv_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->unsignedInteger('resource_id');
            $table->string('name');
            $table->string('type');
            $table->text('config')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_relations');
    }
}
