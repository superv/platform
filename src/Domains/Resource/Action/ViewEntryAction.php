<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Composition;

class ViewEntryAction extends Action
{
    protected $name = 'view';

    protected $title = 'View';

    protected $url;

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', $this->getUrl());
    }

    public function getUrl()
    {
        return $this->url ??'sv/res/{res.handle}/{entry.id}/view';
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}