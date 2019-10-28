<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class ResourceView implements ProvidesUIComponent
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /** @var Closure */
    protected $headingResolver;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $entry;

    protected $actions = [];

    protected $sections;

    public function __construct(Resource $resource, EntryContract $entry)
    {
        $this->resource = $resource;
        $this->entry = $entry;
        $this->sections = collect();
    }

    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-resource-view')
                        ->setProps([
                            'heading' => [
                                'imageUrl' => $imageUrl ?? '',
                                'header'   => $this->resource->getEntryLabel($this->entry),
                            ],
                            'fields'  => $this->getFieldsForView(),
                        ]);
    }

    protected function getFieldsForView()
    {
        return $this->resource->fields()
                              ->keyByName()
                              ->filter(function (FieldInterface $field) {
                                  return ! $field->hasFlag('view.hide');
                              })
                              ->map(function (FieldInterface $field) {
                                  return (new FieldComposer($field))->forView($this->entry);
                              });
    }
}
