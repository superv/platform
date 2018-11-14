<?php

namespace SuperV\Platform\Domains\Resource\Table;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SuperV\Platform\Domains\Resource\Action\EditEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery;
use SuperV\Platform\Domains\Resource\Field\Field;

class TableConfig
{
    public $query;

    protected $uuid;

    /**
     * Table title
     *
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $hiddenFields = [];

    /**
     * @var Collection
     */
    protected $actions;

    protected $url;

    protected $built = false;

    /** @var \SuperV\Platform\Domains\Resource\Contracts\ProvidesQuery */
    protected $queryProvider;

    /** @var \SuperV\Platform\Domains\Resource\Contracts\ProvidesFields */
    protected $fieldsProvider;

    public function build(): self
    {
        $this->uuid = Str::uuid();
//
//        $this->fields = $this->fieldsProvider->provideFields()
//                                             ->map(function (Field $field) {
//                                                 if ($field->getConfigValue('hide.table') === true) {
//                                                     return null;
//                                                 }
//
//                                                 return $field;
//                                             })
//                                             ->filter();

        $this->actions = $this->actions ? collect($this->actions) : collect([EditEntryAction::class]);

        // build Url
        $this->url = sv_url('sv/tables/'.$this->uuid());

        $this->built = true;

        $this->cache();

        return $this;
    }

    public function newQuery()
    {
        return $this->queryProvider->newQuery();
    }

    public function compose()
    {
        if (! $this->isBuilt()) {
            throw new Exception('Table Config is not built yet');
        }

        return [
            'config' => [
                'meta'    => [
                    'columns' => $this->getFields()
                                      ->map(function ($field) {
                                          return ['label' => $field->getLabel(), 'name' => $field->getName()];
                                      })
                                      ->all(),
                ],
                'dataUrl' => $this->getUrl(),
            ],
        ];
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function removeColumn(string $name)
    {
        $this->hiddenFields[] = $name;
    }

    public function getFields(): Collection
    {
        return $this->fieldsProvider->provideFields()
                                    ->map(function (Field $field) {
                                        if ($field->getConfigValue('hide.table') === true) {
                                            return null;
                                        }
                                        if (in_array($field->getName(), $this->hiddenFields)) {
                                            return null;
                                        }

                                        return $field;
                                    })
                                    ->filter();
    }

    public function getActions(): ?Collection
    {
        return $this->actions;
    }

    public function setActions($actions): TableConfig
    {
        $this->actions = $actions;

        return $this;
    }

    public function isBuilt(): bool
    {
        return $this->built;
    }

    protected function validate(): void
    {
        if ($this->isBuilt()) {
            throw new Exception('Config is already built');
        }
    }

    public function cache()
    {
        cache()->forever($this->cacheKey(), serialize($this));
    }

    public function cacheKey(): string
    {
        return 'sv:tables:'.$this->uuid();
    }

    public function setQueryProvider(ProvidesQuery $queryProvider): TableConfig
    {
        $this->queryProvider = $queryProvider;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setFieldsProvider($fieldsProvider)
    {
        $this->fieldsProvider = $fieldsProvider;

        return $this;
    }

    public function uuid()
    {
        return $this->uuid;
    }

    public static function fromCache($uuid): ?TableConfig
    {
        if ($config = cache('sv:tables:'.$uuid)) {
            $config = unserialize($config);

            return $config;
        }

        return null;
    }
}