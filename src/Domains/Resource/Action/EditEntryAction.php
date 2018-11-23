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
        $composition->replace('url', $this->getUrl());
    }

    public function getUrl()
    {
        return $this->url ?? $this->makeUrl();
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    protected function makeUrl()
    {
        return 'sv/res/{resource.handle}/{entry.id}/edit';
    }
}