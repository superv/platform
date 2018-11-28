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
        $port = Current::port();
        if (! $port || ! $prefix = $port->prefix()) {
            return $this->to($this->request->getPathInfo());
        }

        return $this->to($this->getPathInfo());
    }

    /**
     * Get the base URL for the request.
     *
     * @param  string $scheme
     * @param  string $root
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

    public function getPathInfo()
    {
        return str_replace_last('/'.Current::port()->prefix(), '', $this->request->getPathInfo());
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