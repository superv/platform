<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Select;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Composer as BaseComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends BaseComposer
{
    public function form(?FormInterface $form = null): void
    {
        $options = SelectType::parseOptions(($this->getConfigValue('options', [])));

        $this->payload->set('meta.options', $options);
        $this->payload->set('placeholder', __('Select :Object', ['object' => $this->field->getPlaceholder()]));
    }

    public function view(EntryContract $entry): void
    {
        if ($value = $this->payload->get('value')) {
            $options = $this->getConfigValue('options', []);

            $this->payload->set('value', array_get($options, $value, $value));
        }
    }

    public function table(?EntryContract $entry = null): void
    {
        if ($value = $this->payload->get('value')) {
            $options = $this->getConfigValue('options', []);

            $this->payload->set('value', array_get($options, $value, $value));
        }
    }
}