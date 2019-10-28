<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use Tests\Platform\Domains\Resource\ResourceTestCase;

class BelongsToManyFieldTest extends ResourceTestCase
{
    function test__rpc_options()
    {
        $users = $this->blueprints()->users();
        $rpcUrl = sv_route('sv::forms.fields', [
            'form'  => $users->getChildIdentifier('forms', 'default'),
            'field' => 'roles',
            'rpc'   => 'options',
        ]);
        $response = $this->getJsonUser($rpcUrl);
        $response->assertOk();
    }

    function test__rpc_options_with_entry()
    {
        $users = $this->blueprints()->users();
        $rpcUrl = sv_route('sv::forms.fields', [
            'form'  => $users->getChildIdentifier('forms', 'default'),
            'field' => 'roles',
            'rpc'   => 'options',
        ]);

        $user = $users->fake();
        $response = $this->getJsonUser($rpcUrl.'?entry='.$user->getId());
        $response->assertOk();
    }

    function test__rpc_values()
    {
        $this->withoutExceptionHandling();

        $users = $this->blueprints()->users();
        $user = $users->fake();

        $rpcUrl = sv_route('sv::forms.fields', [
            'form'  => $users->getChildIdentifier('forms', 'default'),
            'field' => 'roles',
            'rpc'   => 'values',
        ]);
        $response = $this->getJsonUser($rpcUrl.'?entry='.$user->getId());
        $response->assertOk();
    }
}
