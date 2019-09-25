<?php

namespace SuperV\Platform\Domains\Resource\Field\Jobs;

use Exception;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class ParseFieldRules
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field
     */
    protected $field;

    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    public function parse(?EntryContract $entry = null, string $table = null)
    {
        $field = $this->field;

        $rules = $field->getRules();

        try {
            $resourceConfig = ResourceConfig::find($field->identifier()->getParent());
        } catch (Exception $e) {
        }

        if ($field->isUnique()) {
            $rules[] = sprintf(
                'unique:%s,%s,%s,id',
                $resourceConfig->getTable(),
                $field->getColumnName(),
                $entry ? $entry->getId() : 'NULL'
            );
        }
        if ($field->isRequired()) {
            if ($entry && $entry->exists) {
                $rules[] = 'sometimes';
            }
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        return collect($rules)
            ->map(function ($rule) {
                if (is_array($rule)) {
                    return $rule['rule'];
                }

                return $rule;
            })
            ->filter()
            ->all();
    }
}
