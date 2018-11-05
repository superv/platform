<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class CreateResourceFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('sv_resource_fields', function (Blueprint $table) {
            Blueprints::fields($table);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_resource_fields');
    }
}
