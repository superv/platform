<?php

namespace SuperV\Platform\Domains\Feature;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Exceptions\ValidationException;
use SuperV\Platform\Support\Collection;
use SuperV\Platform\Support\Composer\Composer;

class FeatureBus implements Responsable
{
    protected $handler;

    /** @var \SuperV\Platform\Support\Collection */
    protected $input;

    /** @var \SuperV\Platform\Domains\Feature\Feature */
    protected $feature;

    /** @var \SuperV\Platform\Domains\Feature\Response */
    protected $response;

    public function __construct(Response $response)
    {
        $this->input = new Collection(request()->all());
        $this->response = $response;
    }

    /**
     * @param       $featureClass
     * @param array $input
     * @return \SuperV\Platform\Domains\Feature\Feature
     */
    public static function make($featureClass, array $input)
    {
        return app(self::class)->setRequest($input)->handle($featureClass)->getFeature();
    }

    /** @return self */
    public function instance()
    {
        return $this;
    }

    /** @return self */
    public function handle($featureClass = null)
    {
        if ($featureClass) {
            $this->handler($featureClass);
        }

        $this->resolveHandler();

        $this->feature->init();

        try {
            if ($featureRequest = $this->resolveRequest()) {
                $featureRequest->init($this->getInput());
                $featureRequest->make();
//                $this->feature->setRequest($featureRequest);
            } else {
                $this->input->each(function ($value, $key) {
                    $this->feature->setParam($key, $value);
                });
            }

            $this->feature->run();

            $this->setResponseData();
        } catch (ValidationException $e) {
            $this->response->error($e->getErrors(), 422);
        } catch (FeatureException $e) {
            \Log::error($e->getMessage());
            $this->response->error($e->getMessage(), 425);
        }

        $data = [
            'id'       => $loggerRequestId = session()->pull('logger_request_id'),
            'feature'  => $featureClass,
            'request'  => isset($featureRequest) ? $featureRequest->toArray() : request()->all(),
            'response' => $json = json_encode($this->response->toArray()),
        ];

        \Log::channel('api')->debug($loggerRequestId, $data);

        if ($loggerRequestId) {
            \DB::table('logger_requests')->where('id', $loggerRequestId)->update(['response' => $json]);
        }

        return $this;
    }

    /**
     * Handle feature, get response data.
     *
     * @param null $key
     * @return mixed
     */
    public function get($key = null)
    {
        return $this->handle()->getFeatureResponse()->getData($key);
    }

    public function merge(array $request)
    {
        if (! empty($request)) {
            $this->input = $this->input->merge($request);
        }

        return $this;
    }

    protected function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     * @return FeatureBus
     */
    public function setRequest($input)
    {
        $this->input = new Collection($input);

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return $this->response->toResponse($request);
    }

    /**
     * @return \SuperV\Platform\Domains\Feature\Feature
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * @return \SuperV\Platform\Domains\Feature\Response
     */
    public function getFeatureResponse()
    {
        return $this->response;
    }

    public function getResponseData()
    {
        return $this->response->toArray();
    }

    /**
     * @param string $handler
     * @return FeatureBus
     */
    public function handler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /** @return \SuperV\Platform\Domains\Feature\FeatureBus */
    private function resolveHandler()
    {
        $this->feature = app()->make($this->handler, ['response' => $this->response]);

        return $this;
    }

    /** @return \SuperV\Platform\Domains\Feature\Request|null */
    private function resolveRequest()
    {
        $class = $this->handler.'Request';
        if (! class_exists($class)) {
            return null;
        }

        return app()->make($class, ['feature' => $this->feature]);
    }

    protected function setResponseData(): void
    {
        $composed = (new Composer())->compose($this->feature->getResponseData());

        $this->response->setData($composed);
    }
}