<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Modules\Nucleo\Domains\UI\Page\SvPage;
use SuperV\Modules\Nucleo\Domains\UI\SvBlock;
use SuperV\Modules\Nucleo\Http\Controllers\Concerns\ResolvesResource;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ResourceController extends BaseController
{
    use ResolvesResource;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:superv-api');
    }

    public function create()
    {
        return $this->buildFormPage();
    }

    public function edit()
    {
        $this->resource()->build();
        $form = new Form();

        $form->addResource($this->resource);
        $formData = $form->build()->compose();

        $tabs = sv_tabs()
            ->addTab(sv_tab('General', SvBlock::make('sv-form-v2')->setProps($formData->toArray()))->autoFetch())
            ->addTab(sv_tab('fds', SvBlock::make('sv-form-v2')->setProps($formData->toArray()))->autoFetch());

        $page = SvPage::make('')->addBlock($tabs);

        $page->build();

        return sv_compose($page);
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    protected function resource()
    {
        if ($this->resource) {
            return $this->resource;
        }
        $resource = request()->route()->parameter('resource');
        $this->resource = ResourceFactory::make(str_replace('-', '_', $resource));

        if (! $this->resource) {
            throw new \Exception("Resource not found [{$resource}]");
        }

        if ($id = request()->route()->parameter('id')) {
            $this->resource()->loadEntry($id);
        }

        return $this->resource;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function buildFormPage(): mixed
    {
        $this->resource()->build();
        $form = new Form();

        $form->addResource($this->resource);
        $formData = $form->build()->compose();

        $page = SvPage::make('')->addBlock(
            SvBlock::make('sv-form-v2')->setProps($formData->toArray())
        );

        $page->build();

        return sv_compose($page);
    }
}