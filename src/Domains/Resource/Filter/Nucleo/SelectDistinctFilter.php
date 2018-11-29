<?php

namespace SuperV\Modules\Nucleo\Domains\Resource\Table\Filter;

class SelectDistinctFilter extends SelectFilter
{
    public function build()
    {
        if ($this->getConfigValue('options')) {
            return;
        }

        $options = $this->resource->resolveModel()
                                  ->newQuery()
                                  ->select($this->slug)
                                  ->distinct()
                                  ->where($this->slug, '!=', null)
                                  ->get()
                                  ->pluck($this->slug, $this->slug)
                                  ->all();

        $this->options($options);
    }
}