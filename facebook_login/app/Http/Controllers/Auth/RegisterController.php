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

    /**
     * Obtain the user information from facebook if user uninstall app from facebook.
     *
     * @return Response
     */
    public function handleProvider_Deauthorize_callback($signed_request)
    {
      // 1 get user information with facebook_id
      // 2 find user in user table
      // 3 update  is_active field fron 1 to 0

      try
      {
          // Get user information from facebook
          // run facebook function for unauthoriz user , when user uninstall app from facebook
          $userinformation=$this->parse_signed_request($signed_request);
      }
      catch (\Exception $e) {

        return redirect('/');
      }

      // check user exist in our database this means user registered in our application
      $user = User::where('facebook_id', $userinformation->user_id)->first();

      // if user  exist update user information in user table
      if($user)
      {
          // update user  is_active field
          $flight->is_active = '0';
          $flight->save();
          // redirect user to wellcome page
          return redirect('/');
      }

    }

    //facebook has this instruction for Deauthorize_callback
    /*
    What this means is that when it is POSTed to your app, you will need to parse and verify it before it can be used. This is performed in three steps:
    Split the signed request into two parts delineated by a '.' character (eg. 238fsdfsd.oijdoifjsidf899)
    Decode the first part - the encoded signature - from base64url
    Decode the second part - the payload - from base64url and then decode the resultant JSON object
    */
    public function parse_signed_request($signed_request)
    {
      list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        $secret = "8f26daaae2db5734645d5d9f053f4985"; // Use your app secret here

        // decode the data
        $sig = base64_url_decode($encoded_sig);
        $data = json_decode(base64_url_decode($payload), true);

        // confirm the signature
        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
          error_log('Bad Signed JSON signature!');
          return null;
        }

        return $data;
      }


    public function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

}
