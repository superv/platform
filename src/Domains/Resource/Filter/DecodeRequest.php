<?php

namespace SuperV\Platform\Domains\Resource\Filter;

use Illuminate\Http\Request;
use SuperV\Platform\Support\Dispatchable;

class DecodeRequest
{
    use Dispatchable;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    protected $key;

    public function __construct(Request $request, $key)
    {
        $this->request = $request;
        $this->key = $key;
    }

    public function handle()
    {
        $value = $this->request->get($this->key);

        if ($decoded = base64_decode($value)) {
            if ($hydrated = json_decode($decoded, true)) {
                if (is_array($hydrated)) {
                    return $hydrated;
                }
            }
        }

        return null;
    }
}