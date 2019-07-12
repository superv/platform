<?php

namespace SuperV\Platform\Domains\Resource\Relation\Types;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Action\ModalAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Relation\Relation;

class HasMany extends Relation implements ProvidesTable, ProvidesForm
{
    public function makeTable()
    {
        return $this->getRelatedResource()->resolveTable()
                    ->setQuery($this)
                    ->setDataUrl(sv_url()->path().'/data')
                    ->addContextAction(
                        ModalAction::make('New '.str_singular(str_unslug($this->getName())))
                                   ->setModalUrl($this->route('create', $this->parentEntry))
                                   ->setIdentifier('create_'.$this->getName())
                    );
    }

    public function makeForm(): Form
    {
        $form = FormBuilder::buildFromEntry($childEntry = $this->newQuery()->make());
        $formData = FormModel::findByUuid($this->getRelatedResource()->getHandle());

        return $form
            ->make($formData ? $formData->uuid : null)
            ->hideField(sv_resource($this->parentEntry)->getResourceKey());
    }

    public function getFormTitle(): string
    {
    }

    protected function newRelationQuery(?EntryContract $relatedEntryInstance = null): EloquentRelation
    {
        if (! $localKey = $this->relationConfig->getLocalKey()) {
            if ($this->parentEntry) {
                $entry = $this->parentEntry;
                $localKey = $entry->getKeyName();
            }
        }

        return new EloquentHasMany(
            $relatedEntryInstance->newQuery(),
            $this->parentEntry,
            $this->relationConfig->getForeignKey() ?? $this->parentEntry->getForeignKey(),
            $localKey ?? 'id'
        );
    }
}