<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;

class CreateMetaTables extends Migration
{
    public function up()
    {
        Schema::create('sv_meta_keys', function (Blueprint $table) {
            $table->increments('id');

            $table->uuid('uuid');
            $table->string('key');
            $table->string('value');
//            $table->belongsTo('sv_meta_values', 'value');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_meta_keys');
    }
}
