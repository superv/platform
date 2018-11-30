<?php

namespace SuperV\Platform\Domains\UI\Page;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class MakePage
{
    use FiresCallbacks;

    protected $url;

    /** @var \SuperV\Platform\Domains\UI\Page\Page * */
    protected $page;

    /** @var \SuperV\Platform\Domains\Resource\Form\Form */
    protected $form;

    /** @var array */
    protected $fields = [];

    /** @var Closure */
    protected $successCallback;

    public function __construct($url)
    {
        $this->url = $url;
        $this->boot();
    }

    protected function boot()
    {
        $this->on('get', function () {
            return MakeComponentTree::dispatch($this->page);
        });
    }

    public function onSuccess(Closure $callback)
    {
        $this->successCallback = $callback;

        return $this;
    }

    public function make(): Page
    {
        $this->page = Page::make('');

        $this->form = FormConfig::make($this->fields)
                                ->setUrl($this->url)
                                ->makeForm();

        $this->page->addBlock($this->form);

        app(RouteRegistrar::class)
            ->globally()
            ->register([
                $this->url         => $this->getCallback('get'),
                'POST@'.$this->url => function (Request $request) {
                    return $this->post($request);
                },
            ]);

        return $this->page;
    }

    public function post(Request $request)
    {
        $this->form->setRequest($request)->save();

        if ($this->successCallback) {
            $response = app()->call($this->successCallback, ['form' => $this->form]);
        }

        return response()->json($response ?? []);
    }

    public function setFields(array $fields): MakePage
    {
        $this->fields = $fields;

        return $this;
    }

    public static function forUrl($url)
    {
        return new static($url);
    }
}