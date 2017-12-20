<?php namespace SuperV\Platform\Domains\Auth\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use SuperV\Modules\Ui\Domains\Form\FormFactory;
use SuperV\Modules\Ui\Domains\Form\Jobs\MakeFormInstance;
use SuperV\Platform\Domains\Auth\Features\Logout;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Http\Controllers\BasePlatformController;

class LoginController extends BasePlatformController
{
    use ServesFeaturesTrait;
    use AuthenticatesUsers, ValidatesRequests;

    protected $redirectTo = '/';

    public function logout()
    {
        return $this->serve(Logout::class);
    }

    public function show(FormFactory $factory)
    {
        $form = $factory->fromJson('login.json');

        $this->dispatch(new MakeFormInstance($form));

        return $this->view->make('superv::auth/login-alt', ['form' => $form]);
    }

    public function showLoginForm()
    {
        return $this->view->make('superv::login');
    }
}