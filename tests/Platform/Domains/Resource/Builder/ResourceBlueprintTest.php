<?php

namespace Tests\Platform\Domains\Resource\Builder;

use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Builder\PrimaryKey;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Types\Select\Blueprint as SelectTypeBlueprint;
use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceBlueprintTest extends ResourceTestCase
{
    function test__creates_blueprint()
    {
        $blueprint = Builder::blueprint('sv.testing.posts', function (Blueprint $resource) {
            $resource->label('The Posts');
            $resource->key('postkey');
            $resource->nav('acp.blog');
            $resource->model(TestPostModel::class);

            $resource->databaseDriver()
                     ->table('tbl_posts', 'default')
                     ->primaryKey(new PrimaryKey('post_id'));
        });

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertInstanceOf(DriverInterface::class, $blueprint->getDriver());

        $this->assertEquals('sv.testing.posts', $blueprint->getIdentifier());
        $this->assertEquals('The Posts', $blueprint->getLabel());
        $this->assertEquals('posts', $blueprint->getHandle());
        $this->assertEquals('postkey', $blueprint->getKey());
        $this->assertEquals('acp.blog', $blueprint->getNav());
        $this->assertEquals(TestPostModel::class, $blueprint->getModel());
        $this->assertEquals('tbl_posts', $blueprint->getDriver()->getParam('table'));
    }

    function test__defaults()
    {
        $blueprint = Builder::blueprint('sv.testing.posts', function (Blueprint $resource) {
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
        $blueprint = Builder::blueprint('sv.testing.user_posts', function (Blueprint $resource) {
            $resource->pivot();
        });

        $this->assertTrue($blueprint->isPivot());
    }

    function test__primary_key()
    {
        $blueprint = Builder::blueprint('sv.testing.posts', function (Blueprint $resource) {
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
        $blueprint = Builder::blueprint('sv.testing.posts', function (Blueprint $resource) {
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
        $blueprint = Builder::blueprint('sv.testing.posts', function (Blueprint $resource) {
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
        $blueprint = Builder::blueprint('sv.testing.posts', function (Blueprint $resource) {
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
        $blueprint = Builder::blueprint('sv.testing.posts', function (Blueprint $resource) {
            $resource->textarea('description');
        });

        $descriptionBlueprint = $blueprint->getField('description');
        $this->assertEquals('textarea', $descriptionBlueprint->getField()->getType());
    }

    function test__select_field()
    {
        $blueprint = Builder::blueprint('sv.testing.posts', function (Blueprint $resource) {
            $resource->select('gender')->options(['m', 'f']);
        });

        $genderBlueprint = $blueprint->getField('gender');
        $this->assertInstanceOf(SelectTypeBlueprint::class, $genderBlueprint);
        $this->assertEquals('select', $genderBlueprint->getField()->getType());
        $this->assertEquals(['m', 'f'], $genderBlueprint->getOptions());
    }
}