<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Exception;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class FieldRules
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract|null
     */
    protected $entry;

    /** @var \Illuminate\Support\Collection */
    protected $rules;

    public function __construct(FieldInterface $field, ?EntryContract $entry = null)
    {
        $this->field = $field;
        $this->entry = $entry;

        $this->rules = collect($this->field->getRules());

        $this->parse();
    }

    public function parse()
    {
        if ($this->field->isUnique()) {
            try {
                $this->addRule(sprintf(
                    'unique:%s,%s,%s,id',
                    $this->resourceConfig()->getTable(),
                    $this->field->getColumnName(),
                    $this->entry ? $this->entry->getId() : 'NULL'
                ));
            } catch (Exception $e) {
            }
        }
        if ($this->field->isRequired()) {
            $this->addRule('required');
        }

        $this->stripRuleMessages();
    }

    public function get()
    {
        return $this->rules->all();
    }

    public function removeAll()
    {
        $this->rules = collect();
    }

    public function addRule($rule): FieldRules
    {
        $this->rules->push($rule);

        return $this;
    }

    protected function stripRuleMessages()
    {
        $this->rules = $this->rules
            ->map(function ($rule) {
                if (is_array($rule)) {
                    return $rule['rule'];
                }

                return $rule;
            })
            ->filter();
    }

    protected function resourceConfig(): ResourceConfig
    {
        return ResourceConfig::find($this->field->identifier()->getParent());
    }
}
