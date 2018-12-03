<?php

namespace Tests\Platform\Domains\Auth\Guard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\Access\Guard\Guard;
use SuperV\Platform\Domains\Auth\Access\Guard\Guardable;
use SuperV\Platform\Domains\Auth\Access\Guard\HasGuardableItems;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use Tests\Platform\TestCase;

class GuardableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function checks_if_a_user_can_access_a_guardable_item()
    {
        $user = $this->beUser();
        $user->allow('use.item');
        $this->assertTrue($user->canAccess(new GuardableItem));

        $anotherUser = $this->beUser();
        $this->assertFalse($anotherUser->canAccess(new GuardableItem));

        $yetAnotherUser = $this->beUser();
        $yetAnotherUser->allow('*');
        $yetAnotherUser->forbid('use.item');
        $this->assertFalse($yetAnotherUser->canAccess(new GuardableItem));
    }

    /** @test */
    function returns_true_if_item_is_not_guardable()
    {
        $user = $this->beUser();
        $user->forbid('*');

        $this->assertTrue($user->canAccess(new NonGuardableItem));
    }

    /** @test */
    function filters_guardable_items_in_an_array()
    {
        $user = $this->beUser();
        $user->forbid('*');

        $items = [new GuardableItem, new NonGuardableItem];

        $guard = new Guard($user);

        $this->assertEquals([new NonGuardableItem], $guard->filter($items));
    }

    /** @test */
    function filters_guardable_items_in_a_collection()
    {
        $user = $this->beUser();
        $user->forbid('*');

        $items = collect([new GuardableItem, new NonGuardableItem]);

        $guard = new Guard($user);

        $this->assertEquals(collect([new NonGuardableItem]), $guard->filter($items));
    }

    /** @test */
    function filters_guardable_items_in_array_of_mixed()
    {
        $user = $this->beUser();
        $user->forbid('*');

        $items = [new GuardableItem, new NonGuardableItem, collect([new GuardableItem, new NonGuardableItem])];

        $guard = new Guard($user);

        $this->assertEquals([new NonGuardableItem, collect([new NonGuardableItem])], $guard->filter($items));
    }

    /** @test */
    function filters_guardable_items_in_an_object()
    {
        $user = $this->beUser();
        $user->forbid('*');
        $guard = new Guard($user);

        $object = new GuardableObject;
        $object->setGuardableItems([new GuardableItem, new NonGuardableItem]);

        $guard->filter($object);

        $this->assertEquals([new NonGuardableItem], $object->getGuardableItems());
    }

    /** @test */
    function filters_guardable_items_in_collection_in_an_object()
    {
        $user = $this->beUser();
        $user->forbid('*');
        $guard = new Guard($user);

        $object = new GuardableObject;
        $object->setGuardableItems([new GuardableItem,
            new NonGuardableItem,
            collect([new GuardableItem, new NonGuardableItem])]);

        $guard->filter($object);

        $this->assertEquals([new NonGuardableItem, collect([new NonGuardableItem])], $object->getGuardableItems());
    }

    /** @test */
    function guards_entries()
    {
        $order = new TestOrder();
        $order->setUp();

        $user = $this->beUser();
        $user->forbid($entry = TestOrder::create(['title' => 'secret']));
        $this->assertFalse($user->can($entry));

        $entryPublic = TestOrder::create(['title' => 'public']);
        $user->allow($entryPublic);
        $this->assertTrue($user->can($entryPublic));
    }

    /** @test */
    function guards_entries_in_collection()
    {
        $order = new TestOrder();
        $order->setUp();

        $user = $this->beUser();
        $user->forbid(TestOrder::create(['title' => 'a']));
        $user->allow(TestOrder::create(['title' => 'b']));
        $user->allow(TestOrder::create(['title' => 'c']));
        $user->forbid(TestOrder::create(['title' => 'd']));
        $user->allow(TestOrder::create(['title' => 'e']));

        $filtered = sv_guard(TestOrder::all());

        $this->assertEquals(3, $filtered->count());
        $this->assertArrayContains($filtered->pluck('title')->all(), ['b', 'c', 'e']);
    }

    function conditional_guards()
    {
        $cony = new class
        {
            protected $readonly = true;

            public function guardConditions()
            {
                return [
                    'actions.update' => function () {
                        $this->readonly = false;
                    },
                ];
            }
        };
    }

    /**
     * @return \SuperV\Platform\Domains\Auth\User $user
     */
    protected function beUser()
    {
        $this->be($user = $this->newUser());

        return $user;
    }

    /**
     * @param array $overrides
     * @return \SuperV\Platform\Domains\Auth\User $user
     */
    protected function newUser(array $overrides = [])
    {
        $user = factory(User::class)->create($overrides);
        $user->assign('user');

        return $user->fresh();
    }
}

class TestOrder extends Model implements Guardable
{
    public $timestamps = false;

    protected $table = 'test_orders';

    protected $fillable = ['title'];

    public function setUp()
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });
    }

    public function guardKey(): ?string
    {
        return get_called_class().':'.$this->id;
    }
}

class GuardableItem implements Guardable
{
    public function guardKey(): string
    {
        return 'use.item';
    }
}

class NonGuardableItem
{
}

class GuardableObject implements HasGuardableItems
{
    protected $items;

    public function getGuardableItems()
    {
        return $this->items;
    }

    public function setGuardableItems($items)
    {
        $this->items = $items;
    }
}