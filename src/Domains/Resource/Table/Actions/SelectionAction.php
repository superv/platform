<?php

namespace SuperV\Platform\Domains\Resource\Table\Actions;

use SuperV\Platform\Domains\Resource\Action\RequestAction;
use SuperV\Platform\Domains\Resource\Filter\ApplyFilters;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

abstract class SelectionAction extends RequestAction
{
    /** @var \SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface */
    protected $table;

    abstract public function handle($query);

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()->setName('sv-selection-action')
                     ->setProps([
                         'on-complete' => 'reload',
                         'request-url' => $this->getRequestUrl(),
                     ]);
    }

    public function handleRequest(\Illuminate\Http\Request $request)
    {
        $query = $this->table->getQuery();
        $selection = $request->get('selected');

        if ($selection['type'] === 'filter') {
            ApplyFilters::dispatch($this->table->getFilters(), $query, $request);

            $query->whereNotIn($query->getModel()->getQualifiedKeyName(), array_get($selection, 'excluding', []));
        } else {
            $query->whereIn($query->getModel()->getQualifiedKeyName(), array_get($selection, 'including', []));
        }

        return $this->handle($query);
    }

    public function getTable(): TableInterface
    {
        return $this->table;
    }

    public function setTable(TableInterface $table): void
    {
        $this->table = $table;
    }
}