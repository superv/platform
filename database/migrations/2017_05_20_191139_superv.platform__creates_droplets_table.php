<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Migration\Migration;

class SuperVPlatformCreatesDropletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_droplets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('vendor');
            $table->string('slug');
            $table->string('namespace');
            $table->string('path');
            $table->string('type');
            $table->boolean('enabled');
            $table->unique(['namespace', 'name', 'slug']);

            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('platform_droplets');
    }
}
