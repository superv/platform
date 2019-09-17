<?php

namespace Tests\Platform\Domains\Resource\Fixtures;

use Closure;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;

class Blueprints
{
    use ResourceTestHelpers;

    /** @return Resource */
    public function users(?Closure $callback = null)
    {
        $this->groups();
        $this->roles();

        $users = $this->create('t_users',
            function (Blueprint $table, Config $resource) use ($callback) {
                $resource->resourceKey('user');
                $resource->label('Users');

                $table->increments('id');
                $table->string('name')->entryLabel();
                $table->email('email')->unique();
                $table->string('bio')->rules(['string'])->nullable();
                $table->unsignedInteger('age')->nullable()->showOnIndex();

                $table->file('avatar')->config(['disk' => 'fakedisk']);

                $table->belongsTo('t_groups', 'group')->showOnIndex();
                $table->belongsToMany('platform.t_roles', 'roles')
                      ->pivotTable('platform.assigned_roles')
                      ->pivotForeignKey('user_id')
                      ->pivotRelatedKey('role_id')
                      ->pivotColumns(
                          function (Blueprint $pivotTable) {
                              $pivotTable->string('notes');
                          });

                $table->morphToMany('platform.t_actions', 'actions', 'owner', 'platform.assigned_actions', 'action',
                    function (Blueprint $pivotTable) {
                        $pivotTable->string('provision');
                    });

                $table->hasMany('platform.t_posts', 'posts');
                $table->hasMany('platform.t_comments', 'comments');

                if ($callback) {
                    $callback($table);
                }
            });

        return $users;
    }

    /** @return Resource */
    public function comments()
    {
        return $this->create('t_comments', function (Blueprint $table) {
            $table->resourceConfig()->label('Comments');

            $table->increments('id');
            $table->string('comment');
            $table->select('status')->options(['approved', 'pending']);

            $table->belongsTo('t_users', 'user');
        });
    }

    /** @return Resource */
    public function posts($namespace = 'platform')
    {
        return $this->create('t_posts', function (Blueprint $table, Config $config) use ($namespace) {
            $config->label('Posts');
            $config->setNamespace($namespace);

            $table->increments('id');
            $table->string('title')->entryLabel();

            $table->belongsTo('t_users', 'user');
        });
    }

    /** @return Resource */
    public function categories($namespace = 'testing')
    {
        return $this->create('categories',
            function (Blueprint $table, Config $config) use ($namespace) {
                $config->label('Categories');
                $config->setNamespace($namespace);

                $table->increments('id');
                $table->string('title')->entryLabel();
            }
        );
    }

    /** @return Resource */
    public function orders($namespace = 'testing')
    {
        return $this->create('orders',
            function (Blueprint $table, Config $config) use ($namespace) {
                $config->label('Orders');
                $config->setNamespace($namespace);

                $table->increments('id');
                $table->number('number')->entryLabel();
                $table->string('status')->rules('min:99');
                $table->money('items_total')->nullable();
                $table->money('total')->nullable();
            }
        );
    }

    /** @return Resource */
    public function roles()
    {
        $roles = $this->create('t_roles', function (Blueprint $table, Config $resource) {
            $resource->resourceKey('role');
            $table->increments('id');
            $table->string('title')->unique()->entryLabel();
        });

        $roles->create(['id' => 1, 'title' => 'client']);
        $roles->create(['id' => 2, 'title' => 'admin']);
        $roles->create(['id' => 3, 'title' => 'root']);
    }

    /** @return Resource */
    public function actions()
    {
        $actions = $this->create('t_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('action')->unique()->entryLabel();
        });

        $actions->create(['action' => 'create']);
        $actions->create(['action' => 'view']);
        $actions->create(['action' => 'edit']);
        $actions->create(['action' => 'update']);
        $actions->create(['action' => 'delete']);

        return $actions;
    }

    /** @return Resource */
    public function groups()
    {
        $groups = $this->create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        $groups->create(['id' => 1, 'title' => 'Users']);
        $groups->create(['id' => 2, 'title' => 'Clients']);
        $groups->create(['id' => 3, 'title' => 'Admins']);

        return $groups;
    }
}
