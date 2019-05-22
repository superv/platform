<?php

namespace SuperV\Platform\Domains\Routing;

use Current;

class UrlGenerator extends \Illuminate\Routing\UrlGenerator
{
    /**
     * Get the current URL for the request.
     *
     * @return string
     */
    public function current()
    {
        return $this->to($this->path());
    }

    /**
     * Get the base URL for the request.
     *
     * @param string $scheme
     * @param string $root
     * @return string
     */
    public function formatRoot($scheme, $root = null)
    {
        if (is_null($root)) {
            if (is_null($this->cachedRoot)) {
                $this->cachedRoot = $this->forcedRoot ?: $this->getRequestRoot();
            }

            $root = $this->cachedRoot;
        }

        return parent::formatRoot($scheme, $root);
    }

    public function path()
    {
        $pathInfo = $this->request->getPathInfo();
        $port = Current::port();

        if (! $port || ! $port->prefix()) {
            return $pathInfo;
        }

        return str_replace_last('/'.$port->prefix(), '', $pathInfo);
    }

    private function getRequestRoot()
    {
        if (! $port = Current::port()) {
            return $this->request->root();
        }

        $root = $this->request->getScheme().'://'.$port->root();

        return $root;
    }
}