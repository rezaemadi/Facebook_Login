<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Socialite;
use View;
use Auth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from facebook.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        // 1 check if the user exists in our database with facebook_id
        // 2 if not create a new user
        // 3 login this user into our application

        try
        {
            // Get user information from facebook
            $socialUser = Socialite::driver('facebook')->user();
            $useravatar= $socialUser->avatar;
        }
        catch (\Exception $e) {

          return redirect('/');
        }

        // check user exist in our database this means user registered in our application
        $user = User::where('facebook_id', $socialUser->getId())->first();

        // if user not exist create a new user object and save user information in user table
        if(!$user)
        {
            // create new object from User model
            $a = new User();
            $a->name = $socialUser->getName();
            $a->email = $socialUser->getEmail();
            $a->facebook_id = $socialUser->getId();
            $a->avatar = $socialUser->getAvatar();

            // is active use for when user delete app from facebook change it to '0' and show normal page to user
            $a->is_active ='1';

            // set default password to user by current time
            date_default_timezone_set('Europe/Berlin');
            $current_date = date('Y-m-d H:i:s', time());

            $a->password = bcrypt($current_date);
            $a->save();
        }

        // user authentication is true to login in application directly without show normal login form
        Auth::login($user, true);
        // by data array we can pss every information to view for show to user or for setting
        $data =array('user'=> $user,);
        // show dashboard form(view) to current user that login via facebook
        return View::make("home",$data);

    }

}
