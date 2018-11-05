<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class CreateResourcesTable extends Migration
{
    public function up()
    {
        Schema::create('sv_resources', function (Blueprint $table) {
            Blueprints::resources($table);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_resources');
    }
}
