<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Http\Controllers\BaseApiController;

class FormController extends BaseApiController
{
    public function handle()
    {
        if (! $for = $this->request->get('for')) {
            return PlatformException::fail('Invalid form request');
        }

        if ($res = array_get($for, 'res')) {
            $resource = ResourceFactory::make($res);
            $form = Form::for($resource)
                        ->setUrl($resource->route('store'))
                        ->setRequest($this->request)
                        ->make();

            if ($callback = $resource->getCallback('creating')) {
                app()->call($callback, ['form' => $form]);
            }

            return $form->makeComponent();
        }
    }
}