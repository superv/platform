<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\SchemaService;
use SuperV\Platform\Domains\Resource\Action\EditEntryAction;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesRelationQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Actions\DetachAction;
use SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Actions\LookupAction;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class RelatesToManyType extends FieldType implements
    ProvidesRelationQuery,
    ProvidesTable,
    AcceptsParentEntry
{
    protected $handle = 'relates_to_many';

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceFactory
     */
    protected $factory;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $parentEntry;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $parent;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $related;

    public function __construct(ResourceFactory $factory)
    {
        $this->factory = $factory;
    }

    protected function boot()
    {
        $this->field->addFlag('view.hide');
    }

    public function driverCreating(DriverInterface $driver, FieldBlueprint $blueprint)
    {
        /** @var \SuperV\Platform\Domains\Resource\Field\Types\RelatesToMany\Blueprint $blueprint */
        if ($driver instanceof DatabaseDriver) {
            if ($pivot = $blueprint->getPivot()) {
                if (! SchemaService::resolve()->tableExists($pivot->getHandle())) {
                    Builder::resolve()->save($pivot);
                }
            }
        }
    }

    public function getRelatedEntries(EntryContract $parent)
    {
        return $this->getRelationQuery($parent)->get();
    }

    public function getRelationQuery(EntryContract $parent)
    {
        $parentResource = ResourceFactory::make($parent);

        if (! $pivot = $this->getConfigValue('pivot')) {
            return new EloquentHasMany(
                $this->getRelated()->newQuery(),
                $parent,
                $this->field->getConfigValue('foreign_key', $parent->getForeignKey()),
                $parentResource->config()->getKeyName()
            );
        }

        $pivotResource = ResourceFactory::make($pivot);

        return new EloquentBelongsToMany(
            $this->getRelated()->newQuery(),
            $parent,
            $pivotResource->config()->getTable(),
            $pivotResource->getField($parentResource->config()->getResourceKey())->getConfigValue('foreign_key'),
            $pivotResource->getField($this->getRelated()->config()->getResourceKey())->getConfigValue('foreign_key'),
            $parentResource->config()->getKeyName(),
            $this->getRelated()->config()->getKeyName()
        );
    }

    public function getRelated(): \SuperV\Platform\Domains\Resource\Resource
    {
        if (! $this->related) {
            $this->related = $this->factory->withIdentifier($this->getConfigValue('related'));
        }

        return $this->related;
    }

    public function getPivot(): ?\SuperV\Platform\Domains\Resource\Resource
    {
        if (! $pivot = $this->getConfigValue('pivot')) {
            return null;
        }

        return ResourceFactory::make($pivot);
    }

    public function route($name, EntryContract $entry, array $params = [])
    {
        $params = array_merge([
            'entry'    => $entry->getId(),
            'resource' => $entry->getResourceIdentifier(),
            'relation' => $this->getFieldHandle(),
        ], $params);

        return route('relation.'.$name, $params, false);
    }

    public function makeForm($request = null): \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface
    {
        $builder = FormFactory::builderFromResource($this->getRelated());
        if ($request) {
            $builder->setRequest($request);
        }
        $builder->setEntry($childEntry = $this->getRelationQuery($this->parentEntry)->make());

        $form = $builder->getForm();

        $form->fields()->hide(sv_resource($this->parentEntry)->config()->getResourceKey());

        return $form;
    }

    public function makePivotTable()
    {
        $pivot = ResourceFactory::make($this->getConfigValue('pivot'));
        $pivot->config()->entryLabelField($this->getRelated()->config()->getResourceKey());

        $detachAction = DetachAction::make($this->getRelated()->getChildIdentifier('actions', 'detach'))
                                    ->setParentEntry($this->parentEntry)
                                    ->setField($this->field);

        $attachAction = LookupAction::make($this->getRelated()->getChildIdentifier('actions', 'attach'))
                                    ->setParentEntry($this->parentEntry)
                                    ->setField($this->field);

//        $attachAction = LookupAction::make($this->getRelated()->getChildIdentifier('actions', 'attach'))
//                                    ->lookupUrl($lookupUrl);
//        $viewAction = ViewEntryAction::make($this->getRelated()->getChildIdentifier('actions', 'view'));

        $query = $pivot->newQuery();
        $parentResource = ResourceFactory::make($this->parentEntry);
        $query->where($parentResource->config()->getResourceKey().'_id', $this->parentEntry->getId());

        return $pivot->resolveTable()
                     ->setQuery($query)
//                               ->addRowAction($viewAction)
                     ->addRowAction($detachAction)
                     ->addContextAction($attachAction)
                     ->setDataUrl(url()->current().'/data');
    }

    public function makeTable()
    {
        if ($pivot = $this->getConfigValue('pivot')) {
            return $this->makePivotTable();
        }
        $relatedResource = $this->getRelated();
        $editAction = EditEntryAction::make($relatedResource->getChildIdentifier('actions', 'edit'));

        $query = $this->getRelationQuery($this->parentEntry);

        return $relatedResource->resolveTable()
                               ->setQuery($query)
                               ->setDataUrl(sv_url()->path().'/data')
                               ->addRowAction($editAction)
                               ->addContextAction(
                                   ModalAction::make($relatedResource->getChildIdentifier('actions', 'create'))
                                              ->setTitle('New '.str_singular(str_unslug($this->getFieldHandle())))
                                              ->setModalUrl($this->parentEntry->router()->fieldAction($this->getFieldHandle(), 'create'))
                               );
    }

    public function acceptParentEntry(EntryContract $entry)
    {
        $this->parentEntry = $entry;
    }
}