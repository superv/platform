<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Jobs\DeleteResource;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;

class CreateUsersResource extends Migration
{
    protected $namespace = 'sv_import';

    public function up()
    {
        $this->run('users',
            function (Blueprint $table, Config $config) {
                $config->label('Users');
                $config->nav('acp.app');

                $table->increments('id');
                $table->string('name')->entryLabel();
                $table->integer('age');
            });
    }

    public function down()
    {
        DeleteResource::dispatch('users');
    }
}
