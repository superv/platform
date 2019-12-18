<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;

use SuperV\Platform\Domains\Resource\Field\Composer\FormComposer as BaseFormComposer;
use SuperV\Platform\Domains\Resource\Field\Composer\FormComposerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Composer\Payload;

class FormComposer implements FormComposerInterface
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Composer\FormComposer
     */
    protected $base;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface
     */
    protected $form;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    public function __construct(BaseFormComposer $composer, FormInterface $form, FieldInterface $field)
    {
        $this->base = $composer;
        $this->form = $form;
        $this->field = $field;
    }

    public function compose(): Payload
    {
        $payload = $this->base->compose();
        $entry = $this->form->getEntry();

        if ($entry) {
            if ($relatedEntry = $entry->{$this->field->getHandle()}()->newQuery()->first()) {
                $payload->set('meta.link', $relatedEntry->router()->dashboardSPA());
            }
        }

        $options = $this->field->getConfigValue('meta.options');
        if (! is_null($options)) {
            $payload->set('meta.options', $options);
        } else {
            $route = $this->form->isPublic() ? 'sv::public_forms.fields' : 'sv::forms.fields';
            $url = sv_route($route, [
                'form'  => $this->form->getIdentifier(),
                'field' => $this->field->getHandle(),
                'rpc'   => 'options',
            ]);
            $payload->set('meta.options', $url);
        }
        $payload->set('placeholder', __('Select :Object', [
            'object' => $this->field->getFieldType()->getRelatedResource()->getSingularLabel(),
        ]));

        return $payload;
    }
}