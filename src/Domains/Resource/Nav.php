<?php

namespace SuperV\Platform\Domains\Resource;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Nav\NavModel;

class Nav
{
    /**
     * @var string
     */
    protected $handle;

    public function __construct(string $handle)
    {
        $this->handle = $handle;
        $this->sections = collect();
    }

    public function build()
    {
        $this->sections = NavModel::query()
                                  ->where('nav', $this->handle)
                                  ->get()
                                  ->map(function ($entry) { return $entry->toArray(); })
                                  ->groupBy('section');

        $this->sections->transform(function (Collection $section) {
            return $section->groupBy('subsection');
        });

        return $this;
    }

    public function compose()
    {
        return [
            'sections' => $this->sections->toArray()
        ];
    }

    public function sections(): Collection
    {
        return $this->sections;
    }

    public function section($key): Collection
    {
        return $this->sections->get($key);
    }

    public static function make(string $handle)
    {
        return new static($handle);
    }
}