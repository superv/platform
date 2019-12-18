<?php

namespace Tests\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Builder\PrimaryKey;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Types\Select\Blueprint as SelectTypeBlueprint;
use SuperV\Platform\Domains\Resource\Relation\Types\BelongsTo\Config;
use SuperV\Platform\Domains\Resource\Relation\Types\HasMany\HasManyBlueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceBlueprintTest extends ResourceTestCase
{
    function test__belongs_to_many_relation()
    {
        $rolesBlueprint = Builder::blueprint('testing.roles',
            function (Blueprint $resource) {
                $resource->manyToMany('testing.actions', 'actions')
                         ->pivot('testing.roles_actions', function (Blueprint $pivot) {
                             $pivot->foreignKey('role');
                             $pivot->relatedKey('action');
                         });
            }
        );

        /** @var \SuperV\Platform\Domains\Resource\Relation\Types\BelongsToMany\Config $actions */
        $actionsRelation = $rolesBlueprint->getRelation('actions');
        $this->assertEquals([
            'related_resource'  => 'testing.actions',
            'pivot_identifier'  => 'testing.roles_actions',
            'pivot_table'       => 'roles_actions',
            'pivot_foreign_key' => 'role_id',
            'pivot_related_key' => 'action_id',
        ], $actionsRelation->getConfig());

        $actionsField = $rolesBlueprint->getField('actions');
        $this->assertNotNull($actionsField);
        $this->assertEquals($actionsRelation->getConfig(), $actionsField->getConfig());
    }

    function test__belongs_to_relation()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->belongsTo('testing.users', 'user')
                     ->foreignKey('user_id')
                     ->ownerKey('id');
        });

        /** @var Config $user */
        $user = $blueprint->getRelation('user');
        $this->assertNotNull($user);
        $this->assertEquals('user', $user->getRelationName());
        $this->assertInstanceOf(Config::class, $user);
        $this->assertEquals('testing.users', $user->getRelated());

        $this->assertEquals('id', $user->getOwnerKey());
        $this->assertEquals('user_id', $user->getForeignKey());

        $userField = $blueprint->getField('user');
        $this->assertNotNull($userField);

        $this->assertEquals([
            'related_resource' => 'testing.users',
            'foreign_key'      => 'user_id',
            'owner_key'        => 'id',
        ], $userField->getConfig());
    }

    function test__has_many_relation()
    {
        $blueprint = Builder::blueprint('testing.users', function (Blueprint $resource) {
            $resource->hasMany('testing.posts', 'posts')
                     ->foreignKey('user_id')
                     ->localKey('post_id');
        });

        /** @var HasManyBlueprint $post */
        $post = $blueprint->getRelation('posts');
        $this->assertNotNull($post);
        $this->assertEquals('posts', $post->getRelationName());
        $this->assertInstanceOf(HasManyBlueprint::class, $post);
        $this->assertInstanceOf(Blueprint::class, $post->getParent());
        $this->assertEquals('testing.posts', $post->getRelated());

        $this->assertEquals('post_id', $post->getLocalKey());
        $this->assertEquals('user_id', $post->getForeignKey());
    }

    function test__creates_blueprint()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->key('postkey');

            $resource->databaseDriver()
                     ->table('tbl_posts', 'default')
                     ->primaryKey(new PrimaryKey('post_id'));
        });

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertInstanceOf(DriverInterface::class, $blueprint->getDriver());

        $this->assertEquals('testing.posts', $blueprint->getIdentifier());
        $this->assertEquals('postkey', $blueprint->getKey());
        $this->assertEquals('tbl_posts', $blueprint->getDriver()->getParam('table'));
    }

    function test__defaults()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
//            $resource->id();
        });

        $this->assertEquals('post', $blueprint->getKey());
        $this->assertEquals('posts', $blueprint->getDriver()->getParam('table'));
        $this->assertEquals([
            'name'    => 'id',
            'type'    => 'integer',
            'options' => ['unsigned' => true, 'autoincrement' => true],
        ], $blueprint->getDriver()->getPrimaryKey('id')->toArray());
    }

    function test__pivot_resource()
    {
        $blueprint = Builder::blueprint('testing.user_posts', function (Blueprint $resource) {
            $resource->pivot();
        });

        $this->assertTrue($blueprint->isPivot());
    }

    function test__primary_key()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->primaryKey('post_id');
            $resource->primaryKey('title')->text();
        });
        $postIdKey = $blueprint->getDriver()->getPrimaryKey('post_id');
        $this->assertEquals('post_id', $postIdKey->getName());
        $this->assertEquals(PrimaryKey::NUMBER, $postIdKey->getType());
        $this->assertEquals(['unsigned' => true, 'autoincrement' => true], $postIdKey->getOptions());

        $titleKey = $blueprint->getDriver()->getPrimaryKey('title');
        $this->assertEquals('title', $titleKey->getName());
        $this->assertEquals(PrimaryKey::TEXT, $titleKey->getType());
        $this->assertEquals(['length' => PrimaryKey::DEFAULT_STRING_LENGTH], $titleKey->getOptions());
    }

    function test__field_blueprint()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->text('title', 'Post Title');
            $resource->text('email')->label('Owner Email');
            $resource->text('status')->default('draft');
        });

        $this->assertEquals('Post Title', $blueprint->getField('title')->getLabel());
        $this->assertEquals('Owner Email', $blueprint->getField('email')->getLabel());
        $this->assertEquals('draft', $blueprint->getField('status')->getDefaultValue());
    }

    function test__field_rules()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->text('title')->rules('min:6', 'max:32');
            $resource->number('tries')->rules(['max:10']);
            $resource->text('email')->rules('email|unique');
        });

        $this->assertEquals(['min:6', 'max:32'], $blueprint->getFieldRules('title'));
        $this->assertEquals(['max:10'], $blueprint->getFieldRules('tries'));
        $this->assertEquals(['email', 'unique'], $blueprint->getFieldRules('email'));
    }

    function test__field_flags()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->text('title')->required()->showOnLists();
            $resource->text('email')->unique()->hideOnForms();
            $resource->text('description')->nullable()->hideOnView();
        });

        $this->assertTrue($blueprint->getField('title')->hasFlag('required'));
        $this->assertTrue($blueprint->getField('title')->hasFlag('table.show'));
        $this->assertTrue($blueprint->getField('email')->hasFlag('unique'));
        $this->assertTrue($blueprint->getField('email')->hasFlag('hidden'));
        $this->assertTrue($blueprint->getField('description')->hasFlag('view.hide'));
        $this->assertTrue($blueprint->getField('description')->hasFlag('nullable'));
    }

    function test__textarea_field()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->textarea('description');
        });

        $descriptionBlueprint = $blueprint->getField('description');
        $this->assertEquals('textarea', $descriptionBlueprint->getField()->getType());
    }

    function test__boolean_field()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->boolean('active');
        });

        $activeBlueprint = $blueprint->getField('active');
        $this->assertEquals('boolean', $activeBlueprint->getField()->getType());
    }

    function test__select_field()
    {
        $blueprint = Builder::blueprint('testing.posts', function (Blueprint $resource) {
            $resource->select('gender')->options(['m', 'f']);
        });

        $genderBlueprint = $blueprint->getField('gender');
        $this->assertInstanceOf(SelectTypeBlueprint::class, $genderBlueprint);
        $this->assertEquals('select', $genderBlueprint->getField()->getType());
        $this->assertEquals(['m', 'f'], $genderBlueprint->getConfigValue('options'));
    }
}