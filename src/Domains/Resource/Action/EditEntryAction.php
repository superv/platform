<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Composition;

class EditEntryAction extends Action
{
    protected $name = 'edit';

    protected $title = 'Edit';

    protected $url;

    public function onComposed(Composition $composition)
    {
        $composition->set('url', $this->getUrl());
    }

    public function getUrl()
    {
        return $this->url ?? 'sv/res/{res.handle}/{entry.id}/edit';
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}