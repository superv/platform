<?php

namespace SuperV\Platform\Domains\Entry;

use Illuminate\Routing\UrlGenerator;
use SuperV\Platform\Domains\Manifest\ManifestCollection;

class EntryRouter
{
    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var EntryModel
     */
    protected $entry;

    public function __construct(EntryModel $entry, UrlGenerator $url)
    {
        $this->url = $url;
        $this->entry = $entry;
    }

    public function delete()
    {
        $config = ['model' => get_class($this->entry), 'id' => $this->entry->getId()];
        $ticket = md5(json_encode($config));

        app('cache')->remember(
            'superv::entry.tickets.delete:'.$ticket,
            3600,
            function () use ($config) {
                return $config;
            }
        );

        return $this->url->route('superv::entries.delete', ['ticket' => $ticket]);
    }

    public function edit()
    {
        $config = ['model' => get_class($this->entry), 'id' => $this->entry->getId()];
        $ticket = md5(json_encode($config));

        app('cache')->remember(
            'superv::entry.tickets.edit:'.$ticket,
            3600,
            function () use ($config) {
                return $config;
            }
        );

        return $this->url->route('superv::entries.edit', ['ticket' => $ticket]);
    }

    public function make($route, array $parameters = [])
    {
        if (method_exists($this, $method = camel_case(str_replace('.', '_', $route)))) {
            $parameters['parameters'] = $parameters;

            return app()->call([$this, $method], $parameters);
        }
    }
}
