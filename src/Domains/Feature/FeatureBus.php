<?php

namespace SuperV\Platform\Domains\Feature;

use Illuminate\Contracts\Support\Responsable;
use SuperV\Platform\Exceptions\ValidationException;
use SuperV\Platform\Support\Collection;

class FeatureBus implements Responsable
{
    protected $request;

    /** @var \SuperV\Platform\Domains\Feature\Feature */
    protected $feature;

    /** @var \SuperV\Platform\Domains\Feature\Response */
    protected $response;

    public function __construct()
    {
        $this->request = new Collection(request('payload', request()->all()));
    }

    public function handle($featureClass)
    {
        $this->feature = app($featureClass);
        $this->feature->init();

        /** @var \SuperV\Platform\Domains\Feature\Request $featureRequest */
        $featureRequest = app()->make($featureClass.'Request', ['feature' => $this->feature]);
        $featureRequest->init($this->getRequest());

        try {
            $featureRequest->make();
            $this->feature->setRequest($featureRequest)->run();

            $this->response = Response::ok($this->feature);
        } catch (ValidationException $e) {
            $this->response = Response::error($e->getErrors(), 422);
        } catch (\Exception $e) {
            $this->response = Response::error($e->getMessage(), 425);
        }

        return $this;
    }

    public function mergeRequest(array $request)
    {
        $this->request = $this->request->merge($request);

        return $this;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     * @return FeatureBus
     */
    public function setRequest($request)
    {
        $this->request = new Collection($request);

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
}