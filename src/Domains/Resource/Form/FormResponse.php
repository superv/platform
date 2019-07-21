<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Resource;

class FormResponse implements Responsable
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
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

    public function __construct(Form $form, Resource $resource, EntryContract $entry)
    {
        $this->form = $form;
        $this->resource = $resource;
        $this->entry = $entry;
    }

    public function toResponse($request)
    {
        $action = $request->get('__form_action');

        if ($action === 'view') {
            $route = $this->resource->route('view.page', $this->entry);
        } elseif ($action === 'create_another') {
            $route = $this->resource->route('create');
        } elseif ($action === 'edit_next') {
            $next = $this->resource->newQuery()->where('id', '>', $this->entry->getId())->first();
            if ($next) {
                $route = $this->resource->route('edit.page', $next).'?action=edit_next';
            }
        }

        $entryLabel = $this->resource->getEntryLabel($this->entry);

        return response()->json([
            'data' => [
                'message'     => __($this->form->isUpdating() ? ':Resource :Entry was updated' : ':Resource :Entry was created', ['Entry'    => $entryLabel,
                                                                                                                                  'Resource' => $this->resource->getSingularLabel()]),
                'action'      => $action,
                'redirect_to' => $route ?? $this->resource->route('index'),
            ],
        ]);
    }
}