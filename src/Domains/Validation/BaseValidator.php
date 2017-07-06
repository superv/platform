<?php namespace SuperV\Platform\Domains\Validation;

use Illuminate\Http\Request;
use SuperV\Platform\Contracts\Validator;

abstract class BaseValidator
{
    /**
    	 * @var Validator
    	 */
    	protected $validator;


    	public function __construct(Validator $validator)
    	{
    		$this->validator = $validator;
    	}

    	public function handle(Request $request)
    	{
    		$this->validator->make(
    				$tactic->getRequestData(),
    				$this->rules(),
    				$this->messages()
    		);

    		$result->ok();

    		return $result;
    	}

    	abstract public function rules();
    	abstract public function messages();
}