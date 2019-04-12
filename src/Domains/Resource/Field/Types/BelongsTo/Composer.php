<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Support\Composer\Payload;

class Composer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Types\BelongsTo\BelongsToField
     */
    protected $field;

    public function __construct(BelongsToField $field)
    {
        $this->field = $field;
    }

    protected function formComposer()
    {
        return function (Payload $payload, ?EntryContract $entry = null) {
            if ($entry) {
                if ($relatedEntry = $entry->{$this->field->getName()}()->newQuery()->first()) {
                    $resource = sv_resource($relatedEntry);
                    $payload->set('meta.link', $resource->route('view.page', $relatedEntry));
                }
            }
            $this->field->buildOptions();
            $payload->set('meta.options', $this->field->getResource()->route('fields', null,
                [
                    'field' => $this->field->getName(),
                    'rpc'   => 'options',
                ]));
//            $payload->set('meta.options', $this->>field->options);
            $payload->set('placeholder', 'Select '.$this->field->resolveRelatedResource()->getSingularLabel());
        };
    }

    protected function viewComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if ($relatedEntry = $entry->{$this->field->getName()}()->newQuery()->first()) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view.page', $relatedEntry));
            }
        };
    }

    protected function tableComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if (! $entry->relationLoaded($this->field->getName())) {
                $entry->load($this->field->getName());
            }
            if ($relatedEntry = $entry->getRelation($this->field->getName())) {
                $resource = sv_resource($relatedEntry);
                $payload->set('meta.link', $resource->route('view.page', $relatedEntry));
            }
        };
    }
}