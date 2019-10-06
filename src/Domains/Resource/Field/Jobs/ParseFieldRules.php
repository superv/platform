<?php

namespace SuperV\Platform\Domains\Resource\Field\Jobs;

use Exception;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class ParseFieldRules
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    public function __construct(FieldInterface $field)
    {
        $this->field = $field;
    }

    public function parse(?EntryContract $entry = null)
    {
        $field = $this->field;

        $rules = $field->getRules();

        if ($field->isUnique()) {
            try {
                $rules[] = sprintf(
                    'unique:%s,%s,%s,id',
                    $this->resourceConfig()->getTable(),
                    $field->getColumnName(),
                    $entry ? $entry->getId() : 'NULL'
                );
            } catch (Exception $e) {
            }
        }
        if ($field->isRequired()) {
            if ($entry && $entry->exists()) {
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

    protected function resourceConfig(): ResourceConfig
    {
        return ResourceConfig::find($this->field->identifier()->getParent());
    }
}
