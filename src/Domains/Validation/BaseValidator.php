<?php namespace SuperV\Platform\Domains\Validation;

use Illuminate\Http\Request;
use SuperV\Platform\Contracts\Validator;

abstract class BaseValidator
{
    /**
     * @var Validator
     */
    protected $validator;

    protected $rules;

    protected $messages = [];

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(Request $request)
    {
        $this->validator->make(
            $request->all(),
            $this->rules(),
            $this->messages()
        );
    }

    public function rules()
    {
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
    }
}