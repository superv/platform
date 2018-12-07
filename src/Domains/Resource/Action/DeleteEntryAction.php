<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Payload;

class DeleteEntryAction extends Action
{
    protected $name = 'delete';

    protected $title = 'Delete';

    protected $url;

    public function onComposed(Payload $payload)
    {
        $payload->set('url', $this->getUrl());
    }

    public function getUrl()
    {
        return $this->url ?? 'sv/res/{res.handle}/{entry.id}/delete';
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}