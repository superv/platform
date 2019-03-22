<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class ModalAction extends Action
{
    protected $title;

    protected $identifier;

    protected $modalUrl;

    public function onComposed(Payload $payload)
    {
        $payload->set('url', $this->modalUrl);
    }

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-modal-action')
                     ->setProp('identifier', $this->identifier)
                     ->setProp('button', [
                         'color' => 'sky',
                         'size' => 'sm',
                         'title' => $this->title,
                     ]);
    }

    public function setModalUrl($modalUrl)
    {
        $this->modalUrl = $modalUrl;

        return $this;
    }

    /**
     * @param mixed $identifier
     * @return ModalAction
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }
}