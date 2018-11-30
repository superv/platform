<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class ModalAction extends Action
{
    protected $name = 'sv-modal-action';

    protected $title;

    protected $modalUrl;

    public function onComposed(Payload $payload)
    {
        $payload->set('url', $this->modalUrl);
    }

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()->setName('sv-modal-action');
    }

    public function setModalUrl($modalUrl)
    {
        $this->modalUrl = $modalUrl;

        return $this;
    }
}