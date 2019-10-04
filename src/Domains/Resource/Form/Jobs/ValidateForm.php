<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Jobs\GetRules;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Form\FormFieldCollection;
use SuperV\Platform\Support\Dispatchable;

class ValidateForm
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract|null
     */
    protected $entry;

    public function __construct(FormFieldCollection $fields, FormData $data, ?EntryContract $entry)
    {
        $this->data = $data;
        $this->fields = $fields;
        $this->entry = $entry;
    }

    public function handle(Validator $validator)
    {
        $rules = (new GetRules($this->fields->visible()))->get($this->entry);

        $data = $this->data->getForValidation($this->entry);

        $attributes = $this->fields
            ->map(function (FieldInterface $field) {
                return [$field->getColumnName(), $field->getLabel()];
            })->filter()
            ->toAssoc()
            ->all();

        $validator->make($data, $rules, [], $attributes);
    }

}
