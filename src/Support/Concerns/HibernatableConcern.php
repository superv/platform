<?php

namespace SuperV\Platform\Support\Concerns;

trait HibernatableConcern
{
    /**
     * Hibernate and return wakeup url
     *
     * @return string
     */
    public function hibernate(): string
    {
        $url = sprintf('sv/wake/%s', uuid());

        cache()->forever($url, serialize($this));

        return $url;
    }
}