<?php

use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Schema;

class CreateAddonsTable extends Migration
{
    public function up()
    {
//        Schema::create('sv_addons', function (Blueprint $table, Config $config) {
//            $config->label('Addons');
//            $config->handle('addons');
//            $config->model(AddonModel::class);
//            $config->nav('acp.platform.system');
//
//            $table->increments('id');
//            $table->string('title')->entryLabel()->showOnIndex();
//
//            $table->string('vendor')->showOnIndex();
//            $table->string('handle')->showOnIndex();
//            $table->string('identifier')->showOnIndex()->unique();
//
//            $table->string('path');
//            $table->string('psr_namespace')->nullable();
//            $table->string('type');
//            $table->boolean('enabled')->showOnIndex();
//        });

        SuperV\Platform\Domains\Resource\Builder\Builder::create('sv.platform.addons', function (
            \SuperV\Platform\Domains\Resource\Builder\Blueprint $resource
        ) {
            $resource->label('Addonz');
            $resource->model(AddonModel::class);
            $resource->nav('acp.platform.system');

            $resource->databaseDriver()
                     ->table('sv_addons', 'default');

            $resource->text('title')->useAsEntryLabel()->showOnLists();
            $resource->text('vendor')->showOnLists();
            $resource->text('handle')->showOnLists();
            $resource->text('identifier')->showOnLists()->unique();

            $resource->text('path');
            $resource->text('psr_namespace')->nullable();
            $resource->text('type');
            $resource->boolean('enabled')->showOnLists();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_addons');
    }
}
