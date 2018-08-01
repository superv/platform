<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreatesFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('nucleo_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('prototype_id');
            $table->string('slug');
            $table->string('type');
            $table->boolean('required')->default(true);
            $table->boolean('scatter')->default(false)->nullable();
            $table->string('default_value')->nullable();
            $table->text('rules')->nullable();
            $table->text('config')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('nucleo_fields');
    }
}
