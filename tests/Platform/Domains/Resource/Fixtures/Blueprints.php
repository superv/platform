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
    public function clients(?Closure $callback = null)
    {
        $this->users();

        $clients = $this->create('tbl_clients',
            function (Blueprint $table, Config $config) use ($callback) {
                $config->resourceKey('client');
                $config->setNamespace('testing');
                $config->setName('clients');

                $table->increments('id');
                $table->string('name')->entryLabel();
                $table->belongsTo('platform.users', 'user')->static();

                if ($callback) {
                    $callback($table, $config);
                }
            });

        return $clients;
    }
    /** @return Resource */
    public function users(?Closure $callback = null)
    {
        $this->groups();
        $this->roles();

        $users = $this->create('tbl_users',
            function (Blueprint $table, Config $config) use ($callback) {
                $config->resourceKey('user');
                $config->label('Users');
                $config->setNamespace('testing');
                $config->setName('users');

                $table->increments('id');
                $table->string('name')->entryLabel();
                $table->email('email')->unique();
                $table->string('bio')->rules(['string'])->nullable();
                $table->unsignedTinyInteger('age')->nullable()->showOnIndex();

                $table->file('avatar')->config(['disk' => 'fakedisk'])
                      ->addRule('image');

                $table->belongsTo('testing.groups', 'group')->showOnIndex();

                $table->belongsToMany('testing.roles', 'roles')
                      ->pivotTable('tbl_assigned_roles', 'testing.assigned_roles')
                      ->pivotForeignKey('user_id')
                      ->pivotRelatedKey('role_id')
                      ->pivotColumns(
                          function (Blueprint $pivotTable) {
                              $pivotTable->string('status');
                          });

                $table->morphToMany('testing.actions', 'actions', 'owner')
                      ->pivotRelatedKey('action_id')
                      ->pivotTable('assigned_actions', 'testing.assigned_actions')
                      ->pivotColumns(
                          function (Blueprint $pivotTable) {
                              $pivotTable->string('provision');
                          });

                $table->hasMany('testing.posts', 'posts');
                $table->hasMany('testing.comments', 'comments');

                if ($callback) {
                    $callback($table, $config);
                }
            });

        return $users;
    }

    /** @return Resource */
    public function comments($namespace = 'testing')
    {
        return $this->create('comments', function (Blueprint $table, Config $config) use ($namespace) {
            $config->label('Comments');
            $config->setName('comments');
            $config->setNamespace($namespace);

            $table->increments('id');
            $table->string('comment');
            $table->select('status')->options(['approved', 'pending']);

            $table->belongsTo('testing.users', 'user');
        });
    }

    /** @return Resource */
    public function posts($namespace = 'testing')
    {
        return $this->create('tbl_posts', function (Blueprint $table, Config $config) use ($namespace) {
            $config->label('Posts');
            $config->setName('posts');
            $config->setNamespace($namespace);

            $table->increments('id');
            $table->string('title')->entryLabel();

            $table->belongsTo('testing.users', 'user');
        });
    }

    /** @return Resource */
    public function categories(?Closure $callback = null): Resource
    {
        return $this->create('tbl_categories',
            function (Blueprint $table, Config $config) use ($callback) {
                $config->label('Categories');
                $config->setName('categories');
                $config->setNamespace('testing');

                $table->increments('id');
                $table->string('title')->entryLabel();

                if ($callback) {
                    $callback($table, $config);
                }
            }
        );
    }

    /** @return Resource */
    public function orders($namespace = 'testing')
    {
        return $this->create('tbl_orders',
            function (Blueprint $table, Config $config) use ($namespace) {
                $config->label('Orders');
                $config->setName('orders');
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
    public function roles($namespace = 'testing')
    {
        $roles = $this->create('tbl_roles', function (Blueprint $table, Config $config) use ($namespace) {
            $config->resourceKey('role');
            $config->setName('roles');
            $config->setNamespace($namespace);

            $table->increments('id');
            $table->string('title')->unique()->entryLabel();
        });

        $roles->create(['id' => 1, 'title' => 'client']);
        $roles->create(['id' => 2, 'title' => 'admin']);
        $roles->create(['id' => 3, 'title' => 'root']);
    }

    /** @return Resource */
    public function actions($namespace = 'testing')
    {
        $actions = $this->create('tbl_actions', function (Blueprint $table, Config $config) use ($namespace) {
            $config->setNamespace($namespace);
            $config->setName('actions');
            $table->increments('id');
            $table->string('action')->unique()->entryLabel();

            $table->morphToMany('testing.actions', 'actions', 'owner')
                  ->pivotRelatedKey('action_id')
                  ->pivotTable('assigned_actions', 'testing.assigned_actions')
                  ->pivotColumns(
                      function (Blueprint $pivotTable) {
                          $pivotTable->string('provision');
                      });
        });

        $actions->create(['action' => 'create']);
        $actions->create(['action' => 'view']);
        $actions->create(['action' => 'edit']);
        $actions->create(['action' => 'update']);
        $actions->create(['action' => 'delete']);

        return $actions;
    }

    /** @return Resource */
    public function groups($namespace = 'testing')
    {
        $groups = $this->create('tbl_groups', function (Blueprint $table, Config $config) use ($namespace) {
            $config->setNamespace($namespace);
            $config->setName('groups');
            $table->increments('id');
            $table->string('title');
        });

        $groups->create(['id' => 1, 'title' => 'Users']);
        $groups->create(['id' => 2, 'title' => 'Clients']);
        $groups->create(['id' => 3, 'title' => 'Admins']);

        return $groups;
    }
}
