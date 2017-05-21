<?php namespace Merpa\ApiModule\Http\Controller;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use Merpa\SupportModule\Compose\Composer;
use Merpa\SupportModule\Http\Controller\BaseController;

class BaseApiController extends BaseController
{
    use DispatchesJobs;

    protected $container;

    protected $request;

    protected $payload = [];

    protected $code = 200;

    protected $messages = [];

    /** @var  Collection */
    protected $response;

    public function __construct()
    {
        $this->container = app();
        $this->request = app('Illuminate\Http\Request');

        foreach (app('Anomaly\Streams\Platform\Http\Middleware\MiddlewareCollection') as $middleware) {
            $this->middleware($middleware);
        }

        $this->middleware('Anomaly\Streams\Platform\Http\Middleware\SetLocale');
        $this->middleware('Anomaly\Streams\Platform\Http\Middleware\ApplicationReady');

        $this->middleware('cors');
    }

    protected function code($code)
    {
        $this->code = $code;

        return $this;
    }

    protected function error($errors)
    {
        $response = ['status' => false, 'errors' => is_array($errors) ? $errors : [$errors]];

        return response()->json($response, 406);
    }

    protected function message($message)
    {
        $this->messages = array_merge($this->messages, is_array($message) ? $message : [$message]);

        return $this;
    }

    protected function payload($key, $value = null)
    {
        if (!is_array($key) && !is_null($value)) {
            array_set($this->payload, $key, $value);
        } elseif (is_array($key)) {
            $this->payload = array_merge($this->payload, $key);
        }

        return $this;
    }

    protected function response()
    {
        $response = [
            'status' => $this->code == 200,
        ];


        if (!empty($this->payload)) {
            $response['data'] = (new Composer(['user' => $this->user()]))->compose($this->payload);
        }

        if (!empty($this->messages)) {
            $response['messages'] = $this->messages;
        }

        return response()->json($response, $this->code);

    }

}