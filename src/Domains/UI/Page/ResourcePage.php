<?php

namespace SuperV\Platform\Domains\UI\Page;

use SuperV\Platform\Domains\Resource\Contracts\RequiresEntry;

class ResourcePage extends Page
{
    protected $creatable = true;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    public function notCreatable(): Page
    {
        $this->creatable = false;

        return $this;
    }

    public function build($tokens = [])
    {
        $this->buildActions();

        return parent::build($tokens);
    }

    /**
     * @return bool
     */
    public function isCreatable(): bool
    {
        return $this->creatable;
    }

    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): ResourcePage
    {
        $this->resource = $resource;

        return $this;
    }

    public function setEntry(\SuperV\Platform\Domains\Database\Model\Contracts\EntryContract $entry): ResourcePage
    {
        $this->entry = $entry;

        return $this;
    }

    protected function buildActions(): void
    {
        $this->actions = collect($this->actions)->map(function ($action) {
            if (is_string($action)) {
                if (! $action = $this->resource->getAction($action)) {
                    return null;
                }
            }

            if ($action instanceof RequiresEntry) {
                $action->setEntry($this->entry);
            }

            return $action;
        })->filter()->values()->all();
    }
}
