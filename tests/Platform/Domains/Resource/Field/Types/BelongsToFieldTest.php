<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BelongsToFieldTest extends ResourceTestCase
{
    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $userEntry;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $usersGroup;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface|null
     */
    protected $belongsToField;

    function __meta_link_should_be_null_if_logged_in_user_is_not_authorized_to_view_the_related_entry()
    {
        $this->be($this->newUser(['allow' => null]));

        $callback = $this->belongsToField->getCallback('table.composing');
        $callback($payload = new Payload(), $this->userEntry);

        $this->assertNull($payload->get('meta.link'));
    }

    function test__creates_field_type()
    {
        $users = sv_resource('testing.users');

        $this->assertColumnExists('tbl_users', 'group_id');
        $belongsTo = $users->getField('group');

        $this->assertEquals('belongs_to', $belongsTo->getFieldType());
        $this->assertEquals('testing.groups', $belongsTo->getConfigValue('related_resource'));
        $this->assertEquals('group_id', $belongsTo->getConfigValue('foreign_key'));
    }

    function __callbacks()
    {
        $user = $this->newUser(['allow' => 'testing.groups']);
        $this->be($user);

        $callback = $this->belongsToField->getFieldType()->getPresenter('table');
        $this->assertInstanceOf(Closure::class, $callback);
        $this->assertEquals('Users', $callback($this->userEntry));

        $callback = $this->belongsToField->getCallback('table.composing');
        $callback($payload = new Payload(), $this->userEntry);
        $this->assertEquals($this->usersGroup->router()->dashboardSPA(), $payload->get('meta.link'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->blueprints()->users(function (Blueprint $table) {
            $table->getColumn('email')->nullable();
        });

        $users = sv_resource('testing.users');
        $groups = sv_resource('testing.groups');
        $this->usersGroup = $groups->create(['id' => 100, 'title' => 'Users']);
        $this->userEntry = $users->create(['name' => 'J', 'group_id' => 100]);

        $this->belongsToField = sv_resource('testing.users')->getField('group');
    }
}

