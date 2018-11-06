<?php

use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Resource\Support\Blueprints;

class CreateNavigationTable extends Migration
{
    public function up()
    {
        Schema::create('sv_navigation', function (Blueprint $table) {
            Blueprints::navigation($table);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_navigation');
    }
}
