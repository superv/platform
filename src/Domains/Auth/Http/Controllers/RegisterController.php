<?php namespace SuperV\Platform\Domains\Auth\Http\Controllers;

use SuperV\Modules\Ui\Domains\Form\FormFactory;
use SuperV\Modules\Ui\Domains\Form\Jobs\MakeFormInstance;
use SuperV\Platform\Http\Controllers\BasePlatformController;

class RegisterController extends BasePlatformController
{
    protected $redirectTo = '/';

    public function __construct()
    {
        parent::__construct();
//        $this->middleware('guest');
    }

    public function show(FormFactory $factory)
    {
        $form = $factory->fromJson('register.json');

        $this->dispatch(new MakeFormInstance($form));

        $data = [
            'fields' => $form->getFields()->values()->toArray(),
            'config' => $form->getConfig(),
        ];

        return $this->view->make('superv::auth/login-alt', $data);
    }
}