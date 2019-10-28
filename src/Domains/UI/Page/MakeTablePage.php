<?php

namespace SuperV\Platform\Domains\UI\Page;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Table\FormTable;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class MakeTablePage
{
    use FiresCallbacks;

    protected $url;

    /** @var \SuperV\Platform\Domains\UI\Page\Page * */
    protected $page;

    /** @var \SuperV\Platform\Domains\Resource\Form\Form */
    protected $form;

    /** @var array */
    protected $fields = [];

    /** @var array */
    protected $rows = [];

    /** @var Closure */
    protected $successCallback;

    protected $postUrl;

    public function __construct($url)
    {
        $this->url = $url;
        $this->boot();
    }

    protected function boot() { }

    public function register()
    {
        app(RouteRegistrar::class)
            ->globally()
            ->register([
                $this->url         => function () { return $this->getConfig(); },
                $this->url.'/data' => function () { return $this->getData(); },
                'POST@'.$this->url => function (Request $request) { return $this->post($request); },
            ]);

        return $this;
    }

    public function getConfig()
    {
        $this->page = Page::make('');

        $this->page->addBlock(MakeComponentTree::dispatch($this->makeTable()));

        return MakeComponentTree::dispatch($this->page);
    }

    public function getData()
    {
        return $this->makeTable()->build();
    }

    public function post(Request $request)
    {
        return $request->all();
    }

    public function makeTable(): FormTable
    {
        return app(FormTable::class)
            ->setFields($this->fields)
            ->setRows($this->rows)
            ->setPostUrl($this->url);
    }


    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    public static function forUrl($url)
    {
        return new static($url);
    }
}
