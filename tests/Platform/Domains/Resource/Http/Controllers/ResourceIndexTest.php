<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\UI\Components\BaseUIComponent;
use SuperV\Platform\Support\Concerns\Hydratable;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceIndexTest extends ResourceTestCase
{
    function test__bsmllh()
    {
        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age')->showOnIndex();
            $table->belongsTo('t_groups', 'group')->showOnIndex();
        });

        $groups = $this->create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        $usersGroup = $groups->fake(['title' => 'Users']);
        $clientsGroup = $groups->fake(['title' => 'Clients']);
        $adminsGroup = $groups->fake(['title' => 'Admins']);

        $userA = $users->fake(['group_id' => $usersGroup->getId()]);
        $userB = $users->fake(['group_id' => $clientsGroup->getId()]);

        $url = route('resource.index', ['resource' => 't_users']);

        $page = $this->getPageFromUrl($url);

        $table = UIComponent::from($page->getProp('blocks.0'));

        $response = $this->getJsonUser($table->getProp('config.dataUrl'));
        $response->assertOk();

        $rows = $response->decodeResponseJson('data.rows');
        $this->assertEquals(2, count($rows));

        $this->assertEquals(
            [
                'id'    => $userA->getId(),
                'label' => $userA->name,
                'age'   => $userA->age,
                'group' => 'Users',
            ], $rows[0]['values']
        );

        $this->assertEquals(
            [
                'id'    => $userB->getId(),
                'label' => $userB->name,
                'age'   => $userB->age,
                'group' => 'Clients',
            ], $rows[1]['values']
        );
    }

    protected function getPageFromUrl($url)
    {
        $response = $this->getJsonUser($url);
        $response->assertOk();

        return UIComponent::from($response->decodeResponseJson('data'));
    }
}

class UIComponent extends BaseUIComponent
{
    use Hydratable;

    protected $name;

    protected $uuid;

    public function __construct(array $props = [])
    {
        parent::__construct($props);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function from($array)
    {
        $component = new self($array['props']);
        $component->name = $array['component'];
        $component->uuid = $array['uuid'];

        return $component;
    }
}