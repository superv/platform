<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableDataProviderInterface;

class EloquentTableDataProvider implements TableDataProviderInterface
{
    /** @var \Illuminate\Database\Eloquent\Builder */
    protected $query;

    /** @var array */
    protected $pagination;

    /** @var \Illuminate\Database\Eloquent\Collection */
    protected $entries;

    /** @var int */
    protected $rowsPerPage;

    public function setQuery($query): void
    {
        $this->query = $query;
    }

    public function fetch(): void
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $this->query->paginate($this->getRowsPerPage());
        $countBefore = $paginator->getCollection()->count();
        $this->entries = $paginator->getCollection();

        // Repaginate if guard filtered some of the entries..
        // Not ideal but should do the trick for now
        if ($countBefore !== $this->entries->count()) {
            $paginator = new LengthAwarePaginator(
                $this->entries,
                $paginator->total() - ($countBefore - $this->entries->count()),
                $paginator->perPage(),
                $paginator->currentPage()
            );
        }

        $this->pagination = array_except($paginator->toArray(), 'data');
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function getPagination(): array
    {
        return $this->pagination;
    }

    public function setRowsPerPage($count): void
    {
        $this->rowsPerPage = $count;
    }

    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }
}