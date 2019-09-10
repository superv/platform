<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\Contracts\Form;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField as FormFieldContract;

class FormField extends Field implements FormFieldContract
{
    protected $temporal = false;

    /** @var \SuperV\Platform\Domains\Resource\Form\Form */
    protected $form;

    /** @var \SuperV\Platform\Domains\Resource\Form\FieldLocation */
    protected $location;

    public function observe(FormFieldContract $parent, ?EntryContract $entry = null)
    {
        $parent->setConfigValue('meta.on_change_event', $parent->getName().':'.$parent->getColumnName().'={value}');

        $this->mergeConfig([
            'meta' => [
                'listen_event' => $parent->getName(),
                'autofetch'    => false,
            ],
        ]);

        if ($entry) {
            $this->mergeConfig([
                'meta' => [
                    'query'     => [$parent->getColumnName() => $entry->{$parent->getColumnName()}],
                    'autofetch' => false,
                ],
            ]);
        }
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function setForm(Form $form): void
    {
        $this->form = $form;
    }

    public function getLocation(): ?FieldLocation
    {
        return $this->location;
    }

    public function setLocation(FieldLocation $location): void
    {
        $this->location = $location;
    }

    public function setTemporal($temporal)
    {
        $this->temporal = $temporal;
    }

    public function isTemporal(): bool
    {
        return $this->temporal;
    }

    public static function make(array $params): FormFieldContract
    {
        return FieldFactory::createFromArray($params, self::class);
    }
}
