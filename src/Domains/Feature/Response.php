<?php

namespace SuperV\Platform\Domains\Feature;

use Illuminate\Contracts\Support\Responsable;

class Response implements Responsable
{
    protected $status = 'ok';

    protected $statusCode;

    protected $data;

    protected $error;

    public static function ok(Feature $feature, $statusCode = 200)
    {
        return ( new Response())->setData($feature->getResponseData())->setStatusCode($statusCode);
    }

    public static function error($error, $statusCode)
    {
        return ( new Response())->setError($error)->setStatusCode($statusCode);
    }

    /**
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $jsonArray = ['status' => $this->status];

        array_set_if($this->data, $jsonArray, 'data', $this->data);
        array_set_if($this->error, $jsonArray, 'error', $this->error);

        return response()->json($jsonArray, $this->statusCode);
    }

    /**
     * @param mixed $data
     * @return Response
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param mixed $error
     * @return Response
     */
    public function setError($error)
    {
        $this->status = 'error';

        $this->error = is_array($error) ? ['list' => $error] : ['description' => $error];

        return $this;
    }

    /**
     * @param int $statusCode
     * @return Response
     */
    public function setStatusCode(int $statusCode): Response
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}