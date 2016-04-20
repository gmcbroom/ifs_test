<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Country;
use Mail;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Input;

class AuthController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Registration & Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the registration of new users, as well as the
      | authentication of existing users. By default, this controller uses
      | a simple trait to add these behaviors. Why don't you explore it?
      |
     */

use AuthenticatesAndRegistersUsers,
    ThrottlesLogins;

    private $redirectTo = '/home';
    private $loginPath = '/auth/login';
    private $maxLoginAttempts = 5;
    private $lockoutTime = 300;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        return Validator::make($data, [
                    'email' => 'required|email|max:255|unique:users',
                    'title' => 'required|max:255',
                    'first_name' => 'required|max:255',
                    'last_name' => 'required|max:255',
                    'phone' => 'required|min:6|max:18',
                    'company' => 'required|max:255',
                    'address1' => 'required|min:2|max:255',
                    'address2' => 'min:2|max:255',
                    'town' => 'required|min:2|max:255',
                    'county' => 'required|min:2|max:255',
                    'postcode' => 'required|min:2|max:255',
                    'mobile' => 'required|min:2|max:255',
                    'device_id' => 'min:2|max:255',
        ]);
    }

    public function postRegister(Request $request) {

        $action = Input::get('action');

        if ($action == 'register') {
            return $this->register($request);
        } else {
            return redirect("auth/login");
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data) {

        $password = str_random(8);
        $verify_token = str_random(32);

        // Assume User is from the UK
        $country = Country::where('alpha2', '=', 'GB')->firstOrFail();

        $result = User::create([
                    'residential' => false,
                    'email' => $data['email'],
                    'title' => $data['title'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'password' => bcrypt($password),
                    'phone' => $data['phone'],
                    'company' => $data['company'],
                    'address1' => $data['address1'],
                    'address2' => $data['address2'],
                    'town' => $data['town'],
                    'county' => $data['county'],
                    'postcode' => $data['postcode'],
                    'country_id' => $country->alpha2,
                    'mobile' => $data['mobile'],
                    'verify_token' => $verify_token,
                    'device_id' => $data['device_id'],
        ]);

        if ($result) {
            $mydata['email'] = $data['email'];
            $mydata['name'] = $data['first_name'] . ' ' . $data['last_name'];
            $mydata['first_name'] = $data['first_name'];
            $mydata['last_name'] = $data['last_name'];
            $mydata['password'] = $password;
            $mydata['verify_token'] = $verify_token;
            Mail::send(['email.register', 'email.register_text'], ['mydata' => $mydata], function ($message) use ($mydata) {
                $message->from('oldandgrey@gmx.com', 'Admin');
                $message->to($mydata['email'], $mydata['name']);
                $message->subject('Registration Details!');
            });
        }

        return $result;
    }

    public function getTerms() {
        return view('auth.terms');
    }

    public function postTerms() {
        $action = Input::get('action', 'none');

        if ($action == 'accept') {
            return redirect("auth/register");
        } else {
            return redirect("auth/login");
        }
    }

}
