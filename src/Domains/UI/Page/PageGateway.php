<?php

namespace SuperV\Platform\Domains\UI\Page;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class PageGateway implements Responsable
{
    /**
     * @var Page
     */
    private $page;

    public function __construct($page)
    {
        $this->page = $page;
    }

    public function handle()
    {
        $response = $this->page->render();
        if ($response instanceof Response) {
            return $response;
        }

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        if ($this->page->isAjax()) {
            return view()->make('superv::page/ajax', ['page' => $this->page]);
        }
        return view()->make('superv::page/page', ['page' => $this->page]);
    }
}