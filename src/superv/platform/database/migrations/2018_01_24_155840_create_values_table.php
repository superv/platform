<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;

class CreateValuesTable extends Migration
{
    public function up()
    {
        Schema::create('member_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->string('value');
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('member_values');
    }
}
