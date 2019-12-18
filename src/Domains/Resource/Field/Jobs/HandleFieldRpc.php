<?php

namespace SuperV\Platform\Domains\Resource\Field\Jobs;

use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormField;

class HandleFieldRpc
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface
     */
    protected $form;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $fieldEntry;

    public function __construct(FormInterface $form, FieldModel $fieldEntry)
    {
        $this->form = $form;
        $this->fieldEntry = $fieldEntry;
    }

    public function handle(array $request, string $rpcMethod = null)
    {
        $field = $this->makeField();

        if (! $rpcMethod) {
            return [
                'data' => $field->getFormComposer($this->form)->compose()->get(),
            ];
        }

        if ($field->getFieldType() instanceof HandlesRpc) {
            return $field->getFieldType()
                         ->getRpcResult(['method' => $rpcMethod], $request);
        }

        return null;
    }

    public function makeField()
    {
        $field = FieldFactory::createFromEntry($this->fieldEntry, FormField::class);
        $field->setForm($this->form);

        return $field;
    }
}