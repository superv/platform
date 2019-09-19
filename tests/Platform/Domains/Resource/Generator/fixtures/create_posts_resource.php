<?php

use SuperV\Platform\Domains\Database\Migrations\Migration;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Jobs\DeleteResource;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;

class CreatePostsResource extends Migration
{
    protected $namespace = 'sv_import';

    public function up()
    {
        $this->run('posts',
            function (Blueprint $table, Config $config) {
                $config->label('Posts');
                $config->nav('acp.app');

                $table->increments('id');
                $table->string('title')->entryLabel();
                $table->text('body');
                $table->string('subject');
                $table->nullableBelongsTo('categories', 'category', 'category_id');
                $table->belongsTo('users', 'user', 'user_id');
            });
    }

    public function down()
    {
        DeleteResource::dispatch('posts');
    }
}
