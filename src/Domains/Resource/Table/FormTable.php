<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class FormTable extends Table
{
    protected $selectable = false;

    protected $postUrl;

    protected $formData;

    protected function buildRows(Collection $fields): Collection
    {
        return $this->rows->map(
            function ($row) use ($fields) {
                return [
                    'id'     => $row['id'] ?? null,
                    'fields' => $fields
                        ->map(function (FieldInterface $field) use ($row) {
                            return (new FieldComposer($field))->forForm($row);
                        })->values(),
                ];
            });
    }

    public function makeFields(): Collection
    {
        return parent::makeFields()->filter(function (FieldInterface $field) {
            return ! $field->isHidden();
        })->values();
    }

    public function makeComponent(): ComponentContract
    {
        $props = array_merge_recursive([
            'config'    => ['post_url' => $this->getPostUrl()],
            'seed_data' => $this->formData,
        ],
            $this->composeConfig()
        );

        return Component::make('sv-form-table')->card()->setProps($props);
    }

    public function getPostUrl()
    {
        return $this->postUrl;
    }

    public function setPostUrl($postUrl)
    {
        $this->postUrl = $postUrl;

        return $this;
    }

    public function setFormData($formData)
    {
        $this->formData = $formData;

        return $this;
    }
}
