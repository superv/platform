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

    public function __construct(Collection $filters, $query, ?Request $request)
    {
        $this->filters = $filters;
        $this->query = $query;
        $this->request = $request;
    }

    public function handle()
    {
        if (! $this->request) {
            return;
        }

        if ($this->filters->isEmpty()) {
            return;
        }

        if (! $request = $this->request->get('filters')) {
            return;
        }

        if ($decoded = base64_decode($this->request->get('filters'))) {
            if ($hydrated = json_decode($decoded, true)) {
                if (is_array($hydrated)) {
                    $request = $hydrated;
                }
            }
        }

        $this->filters->map(function (Filter $filter) use ($request) {
            $filterValue = array_get($request, $filter->getIdentifier());
            if (!is_null($filterValue)) {
                $filter->applyQuery($this->query, $filterValue);
            }
        });
    }
}