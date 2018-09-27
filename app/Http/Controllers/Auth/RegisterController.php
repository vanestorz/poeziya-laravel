<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\UserActivate;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{

    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:20|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'token' => str_random(40) . time(),
        ]);

        $user->notify(new UserActivate($user));

        return $user;
    }

    public function register(Request $request)
    {
      $this->validator($request->all())->validate();

      event(new Registered($user = $this->create($request->all())));

      return redirect()->route('login')
          ->with(['success' => 'Congratulations! your account is registered, you will shortly receive an email to activate your account.']);
    }

    public function activate($token = null)
    {
      $user = User::where('token', $token)->first();

      if(empty($user)) {
        return redirect()->to('/')
            ->with(['error' => 'Your activation code is either expired or invalid.']);
      }

      $user->update(['token' => null, 'active' => User::ACTIVE]);

      return redirect()->route('login')
          ->with(['success' => 'Congratulations! your account is now activated.']);
    }
}
