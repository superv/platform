<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\MorphMany as EloquentMorphMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\Table;

class MorphMany extends Relation implements ProvidesTable, ProvidesForm
{
    public function makeTable()
    {
        return Table::resolve()
                    ->setResource($this->getRelatedResource())
                    ->setQuery($this)
                    ->setDataUrl(url()->current().'/data')
                    ->addContextAction(
                ModalAction::make('New '.str_singular(str_unslug($this->getName())))
                           ->setModalUrl($this->route('create', $this->parentEntry))
            );
//            ->mergeFields($this->getPivotFields());
    }

    public function makeForm($request = null): Form
    {
        $builder = FormFactory::builderFromResource($this->getRelatedResource());
        if ($request) {
            $builder->setRequest($request);
        }
        $builder->setEntry($childEntry = $this->newQuery()->make());

        return $builder->getForm();
    }

    public function getFormTitle(): string
    {
    }

    protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation
    {
        return new EloquentMorphMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getMorphName().'_type',
            $this->relationConfig->getMorphName().'_id',
            $this->relationConfig->getOwnerKey() ?? 'id',
        );
    }
}
