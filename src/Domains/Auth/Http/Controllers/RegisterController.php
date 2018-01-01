<?php namespace SuperV\Platform\Domains\Auth\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use SuperV\Platform\Domains\Auth\Domains\User\UserModel;
use SuperV\Platform\Domains\Setting\JSON;
use SuperV\Platform\Http\Controllers\BasePlatformController;

class RegisterController extends BasePlatformController
{

    protected $redirectTo = '/';

    public function __construct()
    {
        parent::__construct();
//        $this->middleware('guest');
    }

    public function show() {
        $form = new JSON(storage_path('superv/forms/register.json'));

        return $this->view->make('module::login-alt', ['form' => $form]);
    }

    public function showRegistrationForm()
    {
        return view('auth::register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return redirect($this->redirectPath());
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     *
     * @return UserModel
     */
    protected function create(array $data)
    {

        return UserModel::create([
            'name'  =>  $data['name'],
            'email'      => $data['email'],
            'password'   => bcrypt($data['password']),
        ]);
    }
}