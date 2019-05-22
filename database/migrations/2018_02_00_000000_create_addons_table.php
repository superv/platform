<?php

use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class CreateAddonsTable extends Migration
{
    public function up()
    {
        Schema::create('sv_addons', function (Blueprint $table, ResourceConfig $resource) {
            $resource->label('Addons');
            $resource->model(AddonModel::class);
            $resource->nav('acp.platform.system');

            $table->increments('id');
            $table->string('name');
            $table->string('vendor')->showOnIndex();
            $table->string('slug')->showOnIndex();
            $table->string('path');
            $table->string('namespace');
            $table->string('type');
            $table->boolean('enabled')->showOnIndex();

            $table->createdBy()->updatedBy();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sv_addons');
    }
}
