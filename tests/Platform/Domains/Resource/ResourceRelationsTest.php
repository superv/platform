<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\Fixtures\TestPost;
use Tests\Platform\Domains\Resource\Fixtures\TestRole;
use Tests\Platform\Domains\Resource\Fixtures\TestUser;

class ResourceRelationsTest extends ResourceTestCase
{
    /** @test */
    function creates_has_many_relations()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->hasMany(TestPost::class, 'posts', 'user_id', 'post_id');
        });

        $resource = ResourceModel::withSlug('test_users');

        // shouldnt create a database column for this
        $this->assertEquals(['id', 'name'], \Schema::getColumnListing('test_users'));

        $postsField = $resource->getField('posts');
        $this->assertNotNull($postsField);
        $this->assertNull($postsField->getColumnType());
        $this->assertEquals('relation', $postsField->getFieldType());

        $this->assertEquals([
            'type'        => 'has_many',
            'related'     => TestPost::class,
            'foreign_key' => 'user_id',
            'local_key'   => 'post_id',
        ], $postsField->getConfig());
    }

    /** @test */
    function creates_belongs_to_relations()
    {
        Schema::create('test_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->belongsTo(TestUser::class, 'user', 'user_id', 'post_id');
        });

        $this->assertEquals(['id', 'user_id'], \Schema::getColumnListing('test_posts'));

        $resource = ResourceModel::withSlug('test_posts');

        $userField = $resource->getField('user');
        $this->assertNotNull($userField);

        $this->assertEquals([
            'type'        => 'belongs_to',
            'related'     => TestUser::class,
            'foreign_key' => 'user_id',
            'owner_key'   => 'post_id',
        ], $userField->getConfig());
    }

    /** @test */
    function creates_belongs_to_many_relations()
    {
        /** @test */
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');

            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };

            $table->belongsToMany(TestRole::class, 'roles', 'test_user_roles', 'user_id', 'role_id', $pivotColumns);
        });

        $userResource = ResourceModel::withSlug('test_users');
        $rolesField = $userResource->getField('roles');
        $this->assertNotNull($rolesField);
        $this->assertEquals(['id'], \Schema::getColumnListing('test_users'));

        $this->assertEquals(
            ['id', 'user_id', 'role_id', 'status', 'created_at', 'updated_at'],
            \Schema::getColumnListing('test_user_roles')
        );

        $this->assertEquals([
            'type'              => 'belongs_to_many',
            'related'           => TestRole::class,
            'pivot_table'       => 'test_user_roles',
            'pivot_foreign_key' => 'user_id',
            'pivot_related_key' => 'role_id',
            'pivot_columns'     => ['status'],
        ], $rolesField->getConfig());
    }

    /** @test */
    function creates_morph_to_many_relations()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 'test_assigned_roles', 'role_id', $pivotColumns);
        });

        $userResource = ResourceModel::withSlug('test_users');
        $rolesField = $userResource->getField('roles');
        $this->assertNotNull($rolesField);
        $this->assertEquals(['id'], \Schema::getColumnListing('test_users'));

        $this->assertEquals(
            ['id', 'owner_type', 'owner_id', 'role_id', 'status', 'created_at', 'updated_at'],
            \Schema::getColumnListing('test_assigned_roles')
        );

        $this->assertEquals([
            'type'              => 'morph_to_many',
            'related'           => TestRole::class,
            'pivot_table'       => 'test_assigned_roles',
            'pivot_foreign_key' => 'owner_id',
            'pivot_related_key' => 'role_id',
            'morph_name'        => 'owner',
            'pivot_columns'     => ['status'],
        ], $rolesField->getConfig());
    }

    /** @test */
    function saves_pivot_columns_even_if_pivot_table_is_created_before()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 'test_assigned_roles', 'role_id', $pivotColumns);
        });

        $userResource = ResourceModel::withSlug('test_users');
        $rolesField = $userResource->getField('roles');
        $this->assertEquals(['status'], $rolesField->getConfigValue('pivot_columns'));

        Schema::create('test_admins', function (Blueprint $table) {
            $table->increments('id');
            $pivotColumns = function (Blueprint $pivotTable) {
                $pivotTable->string('status');
            };
            $table->morphToMany(TestRole::class, 'roles', 'owner', 'test_assigned_roles', 'role_id', $pivotColumns);
        });

        $adminResource = ResourceModel::withSlug('test_admins');
        $rolesField = $adminResource->getField('roles');
        $this->assertEquals(['status'], $rolesField->getConfigValue('pivot_columns'));
    }
}
