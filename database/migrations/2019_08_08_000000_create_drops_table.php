<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Drop\DropModel;
use SuperV\Platform\Domains\Drop\DropRepoModel;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;

class CreateDropsTable extends Migration
{
    public function up()
    {
        $this->create('sv_drop_repos', function (Blueprint $table, Config $config) {
            $config->label('Drop Repos');
            $config->model(DropRepoModel::class);
            $config->nav('acp.platform.system');

            $table->increments('id');
            $table->string('namespace');
            $table->string('identifier')->entryLabel();
            $table->string('handler');

            $table->hasMany('platform.sv_drops', 'drops');
        });

        $this->create('sv_drops', function (Blueprint $table, Config $config) {
            $config->label('Drops');
            $config->model(DropModel::class);
            $config->nav('acp.platform.system');

            $table->increments('id');
            $table->belongsTo('sv_drop_repos', 'repo');
            $table->string('key')->entryLabel();
        });
    }

    public function down()
    {
        $this->dropIfExists('sv_drop_repos');
        $this->dropIfExists('sv_drops');
    }
}
