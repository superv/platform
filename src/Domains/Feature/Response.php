<?php

namespace SuperV\Platform\Domains\Feature;

use Illuminate\Contracts\Support\Responsable;

class Response implements Responsable
{
    protected $status = 'ok';

    protected $statusCode = 200;

    protected $data;

    protected $error;

    public function error($error, $statusCode)
    {
        return $this->setError($error)->setStatusCode($statusCode);
    }

    /**
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json($this->toArray(), $this->statusCode);
    }

    /** @return array */
    public function toArray()
    {
        $toArray = ['status' => $this->status];

        array_set_if($this->data, $toArray, 'data', $this->data);
        array_set_if($this->error, $toArray, 'error', $this->error);

        return $toArray;
    }

    public function getData($key = null)
    {
        if ($key) {
            return array_get($this->data, $key);
        }

        return $this->data;
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

    public function isSuccess(): bool
    {
        return $this->status === 'ok' && ($this->statusCode === 200 || $this->statusCode === 201);
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }
}