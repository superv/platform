<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class CreateRelationsTable extends Migration
{
    public function up()
    {
        Schema::create('sv_relations', function (Blueprint $table) {
            Blueprints::relations($table);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_relations');
    }
}
