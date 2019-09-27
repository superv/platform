<?php

namespace SuperV\Platform\Resources\Users;

use SuperV\Platform\Domains\Resource\Action\ResourceEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class UpdatePasswordAction extends ResourceEntryAction implements HandlesRequests
{
    protected $name = 'update_password';

    protected $title = 'Update Password';

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-modal-form')
                     ->setProps([
                         'title'      => 'Update Password',
                         'fields'     => $this->getFormFields(),
                         'identifier' => $this->name,
                         'url'        => $this->getActionUrl(),
                     ]);
    }

    public function handleRequest(\Illuminate\Http\Request $request)
    {
        $this->entry->updatePassword($request['password']);

        return [
            'entry'  => $this->entry,
            'events' => ['update_password:complete'],
            'data'   => ['message' => 'Password Updated'],
        ];
    }

    protected function getActionUrl(): string
    {
        return $this->entry->router()->actions('update_password');
    }

    protected function getFormFields(): array
    {
        return [
            [
                'type'  => 'text',
                'name'  => 'password',
                'label' => 'New Password',
            ],
        ];
    }
}
