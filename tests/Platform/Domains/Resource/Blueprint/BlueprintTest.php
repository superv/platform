<?php

namespace Tests\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Blueprint\Blueprint;
use SuperV\Platform\Domains\Resource\Blueprint\Builder;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Types\Boolean\BooleanField;
use SuperV\Platform\Domains\Resource\Field\Types\Select\SelectField;
use SuperV\Platform\Domains\Resource\Field\Types\Select\SelectFieldBlueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Text\TextField;
use SuperV\Platform\Domains\Resource\Field\Types\Textarea\TextareaField;
use SuperV\Platform\Domains\Resource\Relation\Types\BelongsTo\BelongsToBlueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BlueprintTest extends ResourceTestCase
{
    function test__belongs_to_relation()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->belongsTo('testing.users', 'user')
                     ->foreignKey('user_id')
                     ->ownerKey('id');
        });

        /** @var BelongsToBlueprint $user */
        $user = $blueprint->getRelation('user');
        $this->assertNotNull($user);
        $this->assertEquals('user', $user->getRelationName());
        $this->assertInstanceOf(BelongsToBlueprint::class, $user);
        $this->assertEquals('testing.users', $user->getRelatedResource());

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

    function test__creates_blueprint()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->databaseDriver()
                     ->table('tbl_posts', 'default')
                     ->primaryKey('post_id');
        });

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertInstanceOf(DriverInterface::class, $blueprint->getDriver());

        $this->assertEquals('testing.posts', $blueprint->getIdentifier());
        $this->assertEquals('tbl_posts', $blueprint->getDriver()->getParam('table'));
        $this->assertEquals([
            'name'    => 'post_id',
            'type'    => 'integer',
            'options' => ['unsigned' => true, 'autoincrement' => true],
        ], $blueprint->getDriver()->getParam('primary_keys')[0]);
    }

    function test__default_driver()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->id();
        });

        $this->assertEquals('posts', $blueprint->getDriver()->getParam('table'));
        $this->assertEquals([
            'name'    => 'id',
            'type'    => 'integer',
            'options' => ['unsigned' => true, 'autoincrement' => true],
        ], $blueprint->getDriver()->getParam('primary_keys')[0]);
    }

    function test__primary_key()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->primaryKey('post_id');
            $resource->primaryKey('title', 'string');
        });

        $this->assertEquals(
            [
                [
                    'name'    => 'post_id',
                    'type'    => 'integer',
                    'options' => ['unsigned' => true, 'autoincrement' => true],
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                ],
            ],
            $blueprint->getDriver()->getParam('primary_keys')
        );
    }

    function test__field_blueprint()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
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
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
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
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->text('title')->required();
            $resource->text('email')->unique()->hideOnForms();
            $resource->text('description')->nullable()->hideOnView();
        });

        $this->assertTrue($blueprint->getField('title')->hasFlag('required'));
        $this->assertTrue($blueprint->getField('email')->hasFlag('unique'));
        $this->assertTrue($blueprint->getField('email')->hasFlag('hidden'));
        $this->assertTrue($blueprint->getField('description')->hasFlag('view.hide'));
        $this->assertTrue($blueprint->getField('description')->hasFlag('nullable'));
    }

    function test__text_field()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->text('title')->useAsEntryLabel();
        });

        $titleBlueprint = $blueprint->getField('title');
        $this->assertTrue($titleBlueprint->isEntryLabel());
        $this->assertEquals(TextField::class, $titleBlueprint->getField()->getType());
    }

    function test__textarea_field()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->textarea('description');
        });

        $descriptionBlueprint = $blueprint->getField('description');
        $this->assertEquals(TextareaField::class, $descriptionBlueprint->getField()->getType());
    }

    function test__boolean_field()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->boolean('active');
        });

        $activeBlueprint = $blueprint->getField('active');
        $this->assertEquals(BooleanField::class, $activeBlueprint->getField()->getType());
    }

    function test__select_field()
    {
        $blueprint = Builder::resource('testing.posts', function (Blueprint $resource) {
            $resource->select('gender')->options(['m', 'f']);
        });

        $genderBlueprint = $blueprint->getField('gender');
        $this->assertInstanceOf(SelectFieldBlueprint::class, $genderBlueprint);
        $this->assertEquals(SelectField::class, $genderBlueprint->getField()->getType());
        $this->assertEquals(['m', 'f'], $genderBlueprint->getConfigValue('options'));
    }
}