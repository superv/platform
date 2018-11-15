<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFields;

class FieldsProvider
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Contracts\ProvidesFields
     */
    protected $provider;

    public function __construct(ProvidesFields $provider)
    {
        $this->provider = $provider;
    }

    public function provide(): Collection
    {
        return $this->createFields();
    }

    protected function createFields(): Collection
    {
        return $this->provider->provideFields()->map(function (FieldModel $field) {
            return FieldFactory::createFromEntry($field);
        });
    }
}