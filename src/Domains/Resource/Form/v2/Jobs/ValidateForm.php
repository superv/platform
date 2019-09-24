<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Jobs;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Resource\Field\Jobs\ParseFieldRules;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Exceptions\ValidationException;

class ValidateForm
{
    /**
     * @var \SuperV\Platform\Contracts\Validator
     */
    protected $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function validate(FormInterface $form)
    {
        $rules = $this->getRules($form);
        $data = $this->prepareData($form->getData());

//        dd($data, $rules);

//        PlatformException::debug([$form->getData(), $rules]);

        try {
            $this->validator->make($data, $rules);
            $form->setValid(true);
        } catch (ValidationException $e) {
//            $errors = [];
//            foreach ($e->getErrors() as $key => $message) {
//                $errors[str_replace('_', '.', $key)] = $message;
//            }
//
//            $e->setErrors($errors);

            throw $e;
//            dd($e->getErrors());
        }
    }

    /**
     * @return static
     */
    public static function resolve()
    {
        return app(static::class);
    }

    protected function prepareData($formData)
    {
        $data = [];
        foreach ($formData as $resource => $fields) {
            foreach ($fields as $field => $value) {
                $data[str_replace('.', '__', $resource.'.'.$field)] = $value;
            }
        }

        return $data;
    }

    protected function getRules(FormInterface $form)
    {
        $rules = [];
        foreach ($form->getFields()->getItems() as $field) {
            $identifier = sv_identifier($field->getIdentifier());

            $fieldRules = (new ParseFieldRules($field))->parse();

            $rules[str_replace('.', '__', $identifier->withoutType())] = $fieldRules;
        }

        return $rules;
    }
}
