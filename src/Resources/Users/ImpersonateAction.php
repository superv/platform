<?php

namespace SuperV\Platform\Resources\Users;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Action\ResourceEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\HandlesRequests;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class ImpersonateAction extends ResourceEntryAction implements HandlesRequests
{
    protected $name = 'impersonate';

    protected $title = 'Impersonate';

    /** @var \SuperV\Platform\Domains\Auth\Contracts\User */
    protected $entry;

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()->setName('sv-action')
                     ->setProp('type', 'post-request');
    }

    public function onComposed(Payload $payload)
    {
        $payload->set('url', str_replace('entry.id', '{entry.id}', $this->getRequestUrl()));
        $payload->set('button', [
            'title' => $this->title,
            'color' => 'sky',
            'size'  => 'sm',
        ]);
    }

    public function handleRequest(Request $request)
    {
        return [
            'data' => [
                'redirect' => [
                    'url'    => $this->getLoginUrl(),
                    'target' => 'blank',
                ],
            ],
        ];
    }

    protected function getToken()
    {
        return \JWTAuth::fromUser($this->entry);
    }

    protected function getLoginUrl()
    {
        if (app()->environment() === 'local') {
            return 'http://'.env('SV_ACP_HOST').'/?token='.$this->getToken();
        }

        return 'http://supreme.dev.io/superv?token='.$this->getToken();
    }
}