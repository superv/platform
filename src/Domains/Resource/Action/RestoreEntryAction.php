<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class RestoreEntryAction extends Action
{
    protected $name = 'restore';

    protected $title = 'Restore';

    protected $url;

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-action')
                     ->setProp('type', 'post-request');
    }

    public function onComposed(Payload $payload)
    {
        $payload->set('method', 'post');
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
        return $this->url ?? 'sv/res/{res.identifier}/{entry.id}/restore';
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
