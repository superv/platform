<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\Contracts\Form;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class FormResponse implements Responsable
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\EntryForm
     */
    protected $form;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $entry;

    public function __construct(Form $form, EntryContract $entry, ?Resource $resource = null)
    {
        $this->form = $form;
        $this->resource = $resource;
        $this->entry = $entry;
    }

    public function toResponse($request)
    {
        if (! $this->resource) {
            $this->resource = ResourceFactory::make($this->entry->getResourceIdentifier());
        }
        $action = $request->get('__form_action');

        if ($action === 'view') {
            $route = $this->resource->spaRoute('entry.dashboard', $this->entry, ['section' => 'view']);
        } elseif ($action === 'create_another') {
            $route = $this->resource->spaRoute('dashboard', null, ['section' => 'create']);
        } elseif ($action === 'edit_next') {
            $next = $this->resource->newQuery()->where('id', '>', $this->entry->getId())->first();
            if ($next) {
                $route = $this->resource->route('forms.edit', $next).'?action=edit_next';
            }
        }

        $data = [
            'message'     => $this->getMessage(),
            'action'      => $action,
            'redirect_to' => $route ?? $this->resource->spaRoute('dashboard'),
        ];

        if ($this->form->isCreating()) {
            $data['entry'] = ['id' => $this->entry->getId()];
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    protected function getMessage()
    {
        $transKey = $this->form->isUpdating() ? ':Resource :Entry was updated' : ':Resource :Entry was created';

        $transData = [
            'Entry'    => $this->resource->getEntryLabel($this->entry),
            'Resource' => $this->resource->getLabel(),
        ];

        return __($transKey, $transData);
    }
}
