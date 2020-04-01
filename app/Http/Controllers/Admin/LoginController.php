<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Encryption;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as Response;
use Validator;
use Illuminate\Support\Facades\DB;

class LoginController extends AdminController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    public function index()
    {

$result = DB::select("SELECT game.*, gs.version, gp.id as gp_id, gp.name as gp_name FROM game RIGHT JOIN game_player_session as gs on game.id = gs.game_id RIGHT JOIN game_player as gp on gs.game_player_id = gp.id ");
	
dd($result);        
return view('admin.auth.login');
    }

    public function showLinkRequestForm()
    {
        return view('admin.auth.passwords.email');
    }

    public function login(Request $request)
    {

        // Validate input data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect('login')->withErrors($validator)->withInput();
        }

        $email = $request->get('email');
        $password = $request->get('password');

        $user = User::where('email', $request->email)->first();
        if(!$user || ($user && Encryption::generateHash($password,$user->salt) != $user->password ) ){
            return redirect('login')->withErrors(['message' => 'Wrong combination of email and password'])->withInput();
        }

        Auth::loginUsingId($user->id);

        $access_token = $user->createToken('VoyeurWeb')->accessToken;
        return redirect('/');
    }

    public function logout(){
        $user = Auth::user();
        Auth::logout();
        return redirect('/login');
//        $token = $user->token();
//        $token->revoke();
    }
}
