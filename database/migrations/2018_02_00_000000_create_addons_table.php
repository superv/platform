<?php

use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;

class CreateAddonsTable extends Migration
{
    public function up()
    {
        Schema::create('sv_addons', function (Blueprint $table, Config $config) {
            $config->label('Addons');
            $config->handle('addons');
            $config->model(AddonModel::class);
            $config->nav('acp.platform.system');

            $table->increments('id');
            $table->string('title')->entryLabel()->showOnIndex();

            $table->string('vendor')->showOnIndex();
            $table->string('handle')->showOnIndex();
            $table->string('identifier')->showOnIndex()->unique();

            $table->string('path');
            $table->string('psr_namespace')->nullable();
            $table->string('type');
            $table->boolean('enabled')->showOnIndex();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_addons');
    }
}
