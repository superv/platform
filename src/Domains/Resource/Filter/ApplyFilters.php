<?php

namespace SuperV\Platform\Domains\Resource\Filter;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\Filter\Filter;
use SuperV\Platform\Support\Dispatchable;

class ApplyFilters
{
    use Dispatchable;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $filters;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Collection $filters, $query, Request $request)
    {
        $this->filters = $filters;
        $this->query = $query;
        $this->request = $request;
    }

    public function handle()
    {
        if ($this->filters->isEmpty()) {
            return;
        }

        if (! $request = $this->request->get('filters')) {
            return;
        }

        $this->filters->map(function (Filter $filter) use ($request) {
            if ($filterValue = array_get($request, $filter->getIdentifier())) {
                $filter->apply($this->query, $filterValue);
            }
        });
    }
}