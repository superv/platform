<?php namespace SuperV\Platform\Domains\Auth\Jobs;

use SuperV\Platform\Domains\Validation\BaseValidator;

class ValidateUserData extends BaseValidator
{
    protected $rules = [
        'first_name' => 'required|max:255',
        'last_name'  => 'required|max:255',
        'email'      => 'required|email|max:255|unique:auth_users',
        'password'   => 'required|min:6',
    ];

    protected $messages = [
        'password.confirmed' => 'Lütfen şifre tekrarınızı kontrol ediniz',
        'birthday.date'      => 'Doğum günü geçerli bir tarih olmalıdır',
        'email.unique'       => 'Bu email adresi sistemizde kayıtlı',
        'email.*'            => 'Geçerli bir email adresi giriniz',
    ];
}