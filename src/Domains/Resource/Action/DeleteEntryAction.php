<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class DeleteEntryAction extends Action
{
    protected $name = 'delete';

    protected $title = 'Delete';

    protected $url;

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-action')
                     ->setProp('type', 'post-request');
    }

    public function onComposed(Payload $payload)
    {
        $payload->set('method', 'delete');
        $payload->set('url', $this->getUrl());
        $payload->set('on-complete', 'reload');
        $payload->set('button', ['confirm' => 'Are you sure? This might trigger actions that can not be undone.',
//                                 'color'   => 'danger inverse',
                                 'icon'    => 'trash',
                                 'size'    => 'sm',
        ]);
    }

    public function getUrl()
    {
        return $this->url ?? 'sv/res/{res.identifier}/{entry.id}';
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
