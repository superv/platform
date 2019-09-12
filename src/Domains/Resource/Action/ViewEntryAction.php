<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Payload;

class ViewEntryAction extends Action
{
    protected $name = 'view';

    protected $title = 'View';

    protected $type = 'redirect';

    protected $url = 'sv/res/{res.identifier}/{entry.id}/view';

    public function onComposed(Payload $payload)
    {
        $payload->set('url', $this->getUrl());
        $payload->set('button', [
            'title' => '',
//            'color' => 'primary inverse',
            'icon'  => 'view',
            'size'  => 'sm',
        ]);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
