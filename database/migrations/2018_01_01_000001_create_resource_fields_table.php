<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateResourceFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('platform_resource_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->unsignedInteger('resource_id');

            $table->string('name');
            $table->string('column_type')->nullable();
            $table->string('field_type');
            $table->boolean('required');
            $table->boolean('unique');
            $table->boolean('searchable');

            $table->text('rules')->nullable();
            $table->text('config')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('platform_resource_fields');
    }
}
