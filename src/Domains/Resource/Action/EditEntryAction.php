<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Payload;

class EditEntryAction extends Action
{
    protected $name = 'edit';

    protected $title = 'Edit';

    protected $url;

    public function onComposed(Payload $payload)
    {
        $payload->set('url', $this->getUrl());
        $payload->set('button', [
            'title' => '',
            //            'color' => 'primary inverse',
            'icon'  => 'edit',
            'size'  => 'sm',
        ]);
    }

    public function getUrl()
    {
        return $this->url ?? 'sv/res/{res.handle}/{entry.id}/edit-page';
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}