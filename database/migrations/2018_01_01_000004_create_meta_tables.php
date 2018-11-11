<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class CreateMetaTables extends Migration
{
    public function up()
    {
        Schema::create('sv_meta', function (Blueprint $table) {
            Blueprints::meta($table);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_meta');
    }
}
