<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;

use Closure;
use Current;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\InlinesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\DecoratesFormComposer;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasPresenter;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\Types\RelationFieldType;
use SuperV\Platform\Domains\Resource\Filter\SelectFilter;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Jobs\MakeLookupOptions;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Support\Composer\Payload;

class BelongsToType extends RelationFieldType implements
    RequiresDbColumn,
    ProvidesFilter,
    InlinesForm,
    HandlesRpc,
    HasPresenter,
    SortsQuery,
    DecoratesFormComposer
{
    protected $handle = 'belongs_to';

    protected $component = 'sv_belongs_to_field';

    /** @var array */
    protected $options;

    protected function boot()
    {
//        $this->field->on('form.composing', $this->formComposer());

        $this->field->on('view.presenting', $this->viewPresenter());
        $this->field->on('view.composing', $this->viewComposer());

        $this->field->on('table.presenting', $this->presenter());
        $this->field->on('table.composing', $this->tableComposer());
        $this->field->on('table.querying', function ($query) {
            $query->with($this->getFieldHandle());
        });
    }

    public function getColumnName(): ?string
    {
        return $this->getConfigValue('foreign_key', $this->getFieldHandle().'_id');
    }

    public function driverCreating(
        DriverInterface $driver,
        \SuperV\Platform\Domains\Resource\Builder\FieldBlueprint $blueprint
    ) {
        if ($driver instanceof DatabaseDriver) {
            $driver->getTable()->addColumn($this->getColumnName(), 'integer');
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param                                       $direction
     * @throws \Exception
     */
    public function sortQuery($query, $direction)
    {
        $parentResource = ResourceFactory::make($this->field->identifier()->parent());
        $parentTable = $parentResource->config()->getTable();

        $relation = RelationConfig::create($this->field->getType(), $this->field->getConfig());

        $relatedResource = ResourceFactory::make($relation->getRelatedResource());
        $relatedTable = $relatedResource->config()->getTable();

        $labelField = $relatedResource->fields()->getEntryLabelField();
        $labelFieldColumName = $labelField ? $labelField->getColumnName() : $relatedResource->config()->getKeyName();

        $orderBy = $relatedTable.'_1.'.$labelFieldColumName;

        $joinType = 'leftJoin';
        $query->getQuery()
              ->{$joinType}($relatedTable." AS ".$relatedTable."_1",
                  $relatedTable.'_1.'.$relatedResource->getKeyName(), '=', $parentTable.'.'.$relation->getForeignKey());

        $query->orderBy($orderBy, $direction);
    }

    public function makeFilter(?array $params = [])
    {
        $this->buildOptions($params['query'] ?? null);

        return SelectFilter::make($this->getFieldHandle(), $this->getRelatedResource()->getSingularLabel())
                           ->setAttribute($this->getColumnName())
                           ->setOptions($this->options)
                           ->setDefaultValue($params['default_value'] ?? null);
    }

    public function getRpcResult(array $params, array $request = [])
    {
        if (! $method = $params['method'] ?? null) {
            return null;
        }

        if (method_exists($this, $method = 'rpc'.studly_case($method))) {
            return call_user_func_array([$this, $method], [$params, $request]);
        }
    }

    public function buildOptions(?array $queryParams = [])
    {
        $relatedResource = $this->getRelatedResource();
        $query = $relatedResource->newQuery();
        if ($queryParams) {
            $query->where($queryParams);
        }

        $entryLabel = $relatedResource->config()->getEntryLabel(sprintf("#%s", $relatedResource->getKeyName()));

        if ($entryLabelField = $relatedResource->fields()->getEntryLabelField()) {
            $query->orderBy($entryLabelField->getColumnName(), 'ASC');
        }

        $this->options = $query->get()->map(function (EntryContract $item) use ($entryLabel) {
            if ($keyName = $this->relatedResource->config()->getKeyName()) {
                $item->setKeyName($keyName);
            }

            return ['value' => $item->getId(), 'text' => sv_parse($entryLabel, $item->toArray())];
        })->all();

        return $this->options;
    }

    public function getPresenter(): Closure
    {
        return function (EntryContract $entry) {
            if (! $entry->relationLoaded($this->getFieldHandle())) {
                $entry->load($this->getFieldHandle());
            }

            if ($relatedEntry = $entry->getRelation($this->getFieldHandle())) {
                return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
            }
        };
    }

    public function inlineForm(FormInterface $parent, array $config = []): void
    {
        $this->field->hide();

        $parent->fields()->addFieldFromArray([
            'type'   => 'sub_form',
            'handle' => $this->getFieldHandle(),
            'config' => array_merge([
                'parent_type' => $this,
                'resource'    => $this->getRelatedResource()->getIdentifier(),
            ], $config, $this->getConfigValue('inline', [])),
        ]);
    }

    public function formComposer()
    {
        return function (Payload $payload, FormInterface $form, ?EntryContract $entry = null) {
            if ($entry) {
                if ($relatedEntry = $entry->{$this->getFieldHandle()}()->newQuery()->first()) {
                    $payload->set('meta.link', $relatedEntry->router()->dashboardSPA());
                }
            }

            $options = $this->getConfigValue('meta.options');
            if (! is_null($options)) {
                $payload->set('meta.options', $options);
            } else {
                $route = $form->isPublic() ? 'sv::public_forms.fields' : 'sv::forms.fields';
                $url = sv_route($route, [
                    'form'  => $this->field->getForm()->getIdentifier(),
                    'field' => $this->getFieldHandle(),
                    'rpc'   => 'options',
                ]);
                $payload->set('meta.options', $url);
            }
            $payload->set('placeholder', __('Select :Object', [
                'object' => $this->getRelatedResource()->getSingularLabel(),
            ]));
        };
    }

    public function rpcOptions(array $params, array $request = [])
    {
//        $this->relatedResource = $this->getRelatedResource();

        return (new MakeLookupOptions($this->getRelatedResource(), $request['query'] ?? []))->make();

        return $this->buildOptions($request['query'] ?? []);
    }

    public function presenter()
    {
        return function (EntryContract $entry) {
            if (! $entry->relationLoaded($this->getFieldHandle())) {
                $entry->load($this->getFieldHandle());
            }

            return $this->getRelatedEntryLabel($entry->getRelation($this->getFieldHandle()));
        };
    }

    public function viewPresenter()
    {
        return function (EntryContract $entry) {
            return $this->getRelatedEntryLabel($entry->{$this->getFieldHandle()}()->newQuery()->first());
        };
    }

    public function viewComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            $this->setMetaLink($entry->{$this->getFieldHandle()}()->newQuery()->first(), $payload);
        };
    }

    public function tableComposer()
    {
        return function (Payload $payload, EntryContract $entry) {
            if (! $entry->relationLoaded($this->getFieldHandle())) {
                $entry->load($this->getFieldHandle());
            }
            $this->setMetaLink($entry->getRelation($this->getFieldHandle()), $payload);
        };
    }

    protected function setMetaLink(?EntryContract $relatedEntry = null, Payload $payload)
    {
        if (is_null($relatedEntry)) {
            return;
        }
        if (Current::user()->canNot($relatedEntry->getResourceIdentifier())) {
            return;
        }
        $payload->set('meta.link', $relatedEntry->router()->dashboardSPA());
    }

    public function getFormComposerDecoratorClass()
    {
        return FormComposer::class;
    }
}
