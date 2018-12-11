<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphMany as EloquentMorphMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\ResourceTable;

class MorphMany extends Relation implements ProvidesTable, ProvidesForm
{
    protected function newRelationQuery(EntryContract $relatedEntryInstance): EloquentRelation
    {
        return new EloquentMorphMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getMorphName().'_type',
            $this->relationConfig->getMorphName().'_id',
            'id'
        );
    }

    public function makeTable()
    {
        return app(ResourceTable::class)
            ->setResource($this->getRelatedResource())
            ->setQuery($this)
            ->setDataUrl(url()->current().'/data')
            ->addContextAction(
                ModalAction::make('New '.str_singular(str_unslug($this->getName())))
                           ->setModalUrl($this->route('create', $this->parentEntry))
            );
//            ->mergeFields($this->getPivotFields());
    }

    public function makeForm(): Form
    {
        return Form::for($childEntry = $this->newQuery()->make())
                   ->hideField(sv_resource($this->parentEntry)->getResourceKey())
                   ->make();
    }

    public function getFormTitle(): string
    {
    }
}