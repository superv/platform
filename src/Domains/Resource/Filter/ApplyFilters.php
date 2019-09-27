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

    /** @var array */
    protected $filterValues;

    public function __construct(Collection $filters, $query, ?Request $request)
    {
        $this->filters = $filters;
        $this->query = $query;
        $this->request = $request;
    }

    public function handle()
    {
        if (! $this->validate()) {
            return false;
        }

        $this->decodeRequest();

        $this->filters->map(function (Filter $filter) {
            $filterValue = array_get($this->filterValues, $filter->getIdentifier());
            if (! is_null($filterValue)) {
                $filter->applyQuery($this->query, $filterValue);
            }
        });

        return true;
    }

    protected function validate()
    {
        if (! $this->request) {
            return false;
        }

        if ($this->filters->isEmpty()) {
            return false;
        }

        if (! $this->request->has('filters')) {
            return false;
        }

        return true;
    }

    protected function decodeRequest()
    {
        $this->filterValues = DecodeRequest::dispatch($this->request, 'filters');
    }
}