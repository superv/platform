<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BelongsToTest extends ResourceTestCase
{
    function test__creates_field_type()
    {
        $this->makeGroupResource();

        $users = $this->create('testing.abusers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->entryLabel();
            $table->belongsTo('testing.groups');
        });

        $this->assertColumnExists('abusers', 'group_id');
        $belongsTo = $users->getField('group');

        $this->assertEquals('belongs_to', $belongsTo->getFieldType());
        $this->assertEquals('testing.groups', $belongsTo->getConfigValue('related_resource'));
        $this->assertEquals('group_id', $belongsTo->getConfigValue('foreign_key'));
    }

    function test__presenter()
    {
        $this->makeGroupResource();
        $users = $this->create('testing.abusers',
            function (Blueprint $table, ResourceConfig $resource) {
                $resource->model(BelongsToTestUser::class);
                $table->increments('id');
                $table->string('name')->entryLabel();
                $table->belongsTo('groups', 'group');
            });

        $fakeUser = BelongsToTestUser::create(['name' => 'J', 'group_id' => 100]);
        $belongsTo = $users->getField('group');

        $callback = $belongsTo->getFieldType()->getPresenter('table');
        $this->assertInstanceOf(Closure::class, $callback);

        $this->assertEquals('Users', $callback($fakeUser));
    }

    protected function makeGroupResource(): void
    {
        $groups = $this->create('testing.groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        $groups->create(['id' => 100, 'title' => 'Users']);
        $groups->create(['id' => 110, 'title' => 'Admins']);
    }
}

class BelongsToTestUser extends Entry
{
    public $timestamps = false;

    protected $table = 'abusers';

    protected $guarded = [];

    public function getId()
    {
        return $this->getKey();
    }

    public function wasRecentlyCreated(): bool
    {
        // TODO: Implement wasRecentlyCreated() method.
    }

    public function group()
    {
        $relation = ResourceFactory::make('testing.abusers')->getRelation('group');

        $relation->acceptParentEntry($this);

        return $relation->newQuery();
    }
}
