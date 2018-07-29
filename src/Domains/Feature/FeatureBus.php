<?php

namespace SuperV\Platform\Domains\Feature;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Exceptions\ValidationException;
use SuperV\Platform\Support\Collection;

class FeatureBus implements Responsable
{
    protected $handler;

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

        $featureRequest = $this->resolveRequest();
        $featureRequest->init($this->getInput());

        try {
            $featureRequest->make();
            $this->feature->setRequest($featureRequest)->run();

            $this->response->setData($this->feature->getResponseData());
        } catch (ValidationException $e) {
            $this->response->error($e->getErrors(), 422);
        } catch (FeatureException $e) {
            \Log::error($e->getMessage());
            $this->response->error($e->getMessage(), 425);
        }

        $data = [
            'id'       => $loggerRequestId = session()->pull('logger_request_id'),
            'feature'  => $featureClass,
            'request'  => $featureRequest->toArray(),
            'response' => $json = json_encode($this->response->toArray()),
        ];

        \Log::channel('api')->debug($loggerRequestId, $data);

        if ($loggerRequestId) {
            \DB::table('logger_requests')->where('id', $loggerRequestId)->update(['response' => $json]);
        }

        return $this;
    }

    public function merge(array $request)
    {
        $this->input = $this->input->merge($request);

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

    /** @return \SuperV\Platform\Domains\Feature\Request */
    private function resolveRequest()
    {
        return app()->make($this->handler.'Request', ['feature' => $this->feature]);
    }
}