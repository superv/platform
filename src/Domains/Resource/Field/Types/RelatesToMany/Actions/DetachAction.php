<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Actions;

use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

class DetachAction extends BaseAction
{
    protected $name = 'detach';

    protected $title = 'Detach';

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-action')
                     ->setProp('type', 'post-request');
    }

    public function onComposed(Payload $payload)
    {
        $payload->merge([
            'url'         => str_replace('entry.id', '{entry.id}', $this->getRequestUrl()),
            'on-complete' => 'reload',
            'button'      => [
                'color' => 'warning',
                'size'  => 'sm',
                'title' => __('Detach'),
            ],
        ]);
    }

    public function getRequestUrl()
    {
        $related = $this->field->type()->getRelated();

        return $this->field->router()->route('detach').'?'.http_build_query([
                'entry'   => $this->parentEntry->getId(),
                'field'   => $this->field->getIdentifier(),
                'related' => sprintf('entry.%s', $related->config()->getKeyName()),
            ]);
    }
}