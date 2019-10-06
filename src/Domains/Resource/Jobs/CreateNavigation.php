<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class CreateNavigation
{
    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceConfig
     */
    protected $config;

    public function setConfig(ResourceConfig $config)
    {
        $this->config = $config;

        return $this;
    }

    public function create($nav, $resourceId)
    {
        if (is_string($nav)) {
            Section::createFromString($handle = $nav.'.'.$this->config->getName());
            $section = Section::get($handle);
            $section->update([
                'resource_id' => $resourceId,
                'url'         => 'sv/res/'.$this->config->getIdentifier(),
                'title'       => $this->config->getLabel(),
                'handle'      => str_slug($this->config->getLabel(), '_'),
            ]);
        } elseif (is_array($nav)) {
            if (! isset($nav['url'])) {
                $nav['url'] = 'sv/res/'.$this->config->getIdentifier();
            }
            $section = Section::createFromArray($nav);
        }

        return $section;
    }

    /** *
     * @param \SuperV\Platform\Domains\Resource\ResourceConfig $config
     * @return static
     */
    public static function resolve(ResourceConfig $config)
    {
        return app(static::class)->setConfig($config);
    }
}
