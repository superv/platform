<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Composition;

class DeleteEntryAction extends Action
{
    protected $name = 'delete';

    protected $title = 'Delete';

    protected $url;

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', $this->getUrl());
    }

    public function getUrl()
    {
        return $this->url ??'sv/res/{res.handle}/{entry.id}/delete';
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}