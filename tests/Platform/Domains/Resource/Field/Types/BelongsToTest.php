<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Closure;
use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BelongsToTest extends ResourceTestCase
{
    function test__creates_field_type()
    {
        $this->makeGroupResource();

        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->entryLabel();
            $table->belongsTo('t_groups', 'group');
        });

        $this->assertColumnExists('t_users', 'group_id');
        $belongsTo = $users->getField('group');

        $this->assertEquals('belongs_to', $belongsTo->getFieldType());
        $this->assertEquals('t_groups', $belongsTo->getConfigValue('related_resource'));
        $this->assertEquals('group_id', $belongsTo->getConfigValue('foreign_key'));
    }

    function test__presenter()
    {
        $this->makeGroupResource();
        $users = $this->create('t_users',
            function (Blueprint $table, ResourceConfig $resource) {
                $resource->model(BelongsToTestUser::class);
                $table->increments('id');
                $table->string('name')->entryLabel();
                $table->belongsTo('t_groups', 'group');
            });

        $fakeUser = BelongsToTestUser::create(['name' => 'J', 'group_id' => 100]);
        $belongsTo = $users->getField('group');

        $callback = $belongsTo->getFieldType()->getPresenter('table');
        $this->assertInstanceOf(Closure::class, $callback);

        $this->assertEquals('Users', $callback($fakeUser));
    }

    protected function makeGroupResource(): void
    {
        $groups = $this->create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        $groups->create(['id' => 100, 'title' => 'Users']);
        $groups->create(['id' => 110, 'title' => 'Admins']);
    }
}

class BelongsToTestUser extends Model implements EntryContract
{
    public $timestamps = false;

    protected $table = 't_users';

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
        $relation = ResourceFactory::make('t_users')->getRelation('group');

        $relation->acceptParentEntry($this);

        return $relation->newQuery();
    }
}