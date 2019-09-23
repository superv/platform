<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Action\ResourceEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class UpdatePasswordAction extends ResourceEntryAction implements HandlesRequests
{
    protected $name = 'update_password';

    protected $title = 'Update Password';

    protected $entry;

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-modal-form')
                     ->setProp('title', 'Update Password')
                     ->setProp('fields', [
                         [
                             'type'  => 'text',
                             'name'  => 'password',
                             'label' => 'New Password',
                         ],
                     ])
                     ->setProp('identifier', $this->name)
                     ->setProp('url', url()->current().'/actions/update_password');
    }

    public function handleRequest(\Illuminate\Http\Request $request)
    {
        $this->entry->updatePassword($request['password']);

        return [
            'entry'  => $this->entry,
            'events' =>
                ['update_password:complete'],
            'data'   => ['message' => 'Password Updated'],
        ];
    }
}
