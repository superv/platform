<?php

namespace SuperV\Platform\Domains\UI\Page;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class MakeFormPage
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

    protected $postUrl;

    public function __construct($url)
    {
        $this->url = $url;
        $this->boot();
    }

    protected function boot() { }

    public function onSuccess(Closure $callback)
    {
        $this->successCallback = $callback;

        return $this;
    }

    public function register()
    {
        $this->postUrl = cache()->rememberForever(md5($this->url), function () { return 'sv/frm/'.uuid(); });

        app(RouteRegistrar::class)
            ->globally()
            ->register([
                $this->url             => function () { return $this->get(); },
                'POST@'.$this->postUrl => function (Request $request) {
                    return $this->post($request);
                },
            ]);
    }

    public function get()
    {
        $this->page = Page::make('');

        $this->page->addBlock($this->makeForm());

        return MakeComponentTree::dispatch($this->page);
    }

    public function makeForm()
    {
        return FormConfig::make($this->fields)
                         ->setUrl($this->postUrl)
                         ->makeForm();
    }

    public function post(Request $request)
    {
        $form = $this->makeForm();
        $form->setRequest($request)->save();

        if ($this->successCallback) {
            $response = app()->call($this->successCallback, ['form' => $form]);
        }

        return response()->json($response ?? []);
    }

    public function setFields(array $fields): MakeFormPage
    {
        $this->fields = $fields;

        return $this;
    }

    public static function forUrl($url)
    {
        return new static($url);
    }
}