<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Payload;

class CreateEntryAction extends Action
{
    protected $name = 'create';

    protected $title = 'Create';

    protected $url;

    public function onComposed(Payload $payload)
    {
        $payload->set('url', $this->url ?? 'sv/res/{res.handle}/create');
        $payload->set('button', [
            'color' => 'green',
            'title' => sv_trans('sv::actions.create')
        ]);
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
