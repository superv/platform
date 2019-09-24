<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Payload;

class EditEntryAction extends Action
{
    protected $name = 'edit';

    protected $title = 'Edit';

    protected $type = 'redirect';

    protected $url = 'sv/res/{res.identifier}/{entry.id}/edit';

//    protected $url = 'sv/forms-v2/{res.identifier}.forms:default/{entry.id}/edit';

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
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
