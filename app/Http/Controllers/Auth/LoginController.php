<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';


    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request)
    {
      $field = $this->field($request);

      return [
        $field => $request->get($this->username()),
        'password' => $request->get('password');
        'active' => User::ACTIVE;
      ];
    }

    public function field(Request $request)
    {
      $email = $this->username();

      return filter_var($request->get($email), FILTER_VALIDATE_EMAIL) ? $email : 'username';
    }

    protected function validateLogin(Request $request)
    {
      $field = $this->field($request);

      $messages = ["{$this->username()}.exists" => 'The account you are trying to login is not activated or it has been disabled.'];

      $this->validate($request, [
        $this->username() => "required|exists:users,($field),active" . User::ACTIVE,
            'password' => 'required',
        ], $messages);
    }
}
