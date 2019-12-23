<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToOne;

use Current;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldComposer as BaseComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends BaseComposer
{
    public function view(EntryContract $entry): void
    {
        $relatedEntry = $entry->{$this->getFieldHandle()}()->newQuery()->first();

        if ($relatedEntry) {
            $this->payload->set('value', sv_resource($relatedEntry)->getEntryLabel($relatedEntry));
            if (Current::user()->can($this->field->getConfigValue('related'))) {
                $this->payload->set('meta.link', $relatedEntry->router()->dashboardSPA());
            }
        }
    }

    public function form(?FormInterface $form = null): void
    {
        $entry = $form->getEntry();

        if ($entry) {
            if ($relatedEntry = $entry->{$this->field->getHandle()}()->newQuery()->first()) {
                $this->payload->set('meta.link', $relatedEntry->router()->dashboardSPA());
            }
        }

        $options = $this->field->getConfigValue('meta.options');
        if (! is_null($options)) {
            $this->payload->set('meta.options', $options);
        } else {
            $route = $form->isPublic() ? 'sv::public_forms.fields' : 'sv::forms.fields';
            $url = sv_route($route, [
                'form'  => $form->getIdentifier(),
                'field' => $this->field->getHandle(),
                'rpc'   => 'options',
            ]);
            $this->payload->set('meta.options', $url);
        }
        $this->payload->set('placeholder', __('Select :Object', [
            'object' => $this->field->getFieldType()->getRelated()->getSingularLabel(),
        ]));
    }
}