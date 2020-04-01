<?php

namespace App\Http\Controllers\Api;

use App\Events\Post;
use App\Models\ChatPost;
use App\Models\ChatRoom;
use App\Models\Company;
use App\Models\PasswordReset;
use App\Models\UnreadChatPost;
use App\Notifications\PasswordResetRequest;
use App\Notifications\RegisterActivate;
use App\Rules\Recaptcha;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//use App\User;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Validator;
use App\Helpers\Data;
use App\Helpers\Encryption;
use App\Models\RoomUsers;


use Symfony\Component\HttpFoundation\Response as Response;

class AuthController extends ApiController
{

    /**
     * @OA\Post(
     *         path="/auth/login",
     *         tags={"Authentication"},
     *         summary="Login",
     *         description="Login an user",
     *         operationId="login",
     *
     *         @OA\Response( response=200,description="Successful logged" ),
     *         @OA\Response( response=400, description="Invalid input (Validation errors )" ),
     *         @OA\Response( response=404,description="Wrong combination of email and password or email not verified" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                      required={"email","password"},
     *                      type="object",
     *                      @OA\Property( property="email", description="Email", type="string" ),
     *                      @OA\Property( property="password", description="Password", type="string", format="password" ),
     *                 )
     *             )
     *         )
     * )
     */

    public function login(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $email = $request->get('email');
        $password = $request->get('password');

        $user = User::where('email', $request->email)->first();
        if (!$user || ($user && Encryption::generateHash($password, $user->salt) != $user->password)) {
            return response()->json(['success' => false, 'message' => 'Wrong combination of email and password or email has not been verified'], Response::HTTP_NOT_FOUND);
        }

        Auth::loginUsingId($user->id);

        $access_token = $user->createToken('Token Name')->accessToken;
        $refresh_token = $access_token;
        return response()->json(['success' => true, 'token' => $access_token, 'refresh_token' => $refresh_token], Response::HTTP_OK);

    }

    public function signOut($action = null)
    {
        $user = Auth::user();

        $chatRooms = ChatRoom::select('id')->get();

        foreach ($chatRooms->toArray() as $room) {

            $chatPost = ChatPost::create([
                'chat_room_id' => $room['id'],
                'owner_id' => $user->id,
                'type' => 'room_left'
            ]);

            $chatPost['user_info'] = $user;

            $users = RoomUsers::where('chat_room_id', $room['id'])->pluck('user_id')->toArray();
            $onlyOnlines = array_intersect($users, Data::getOnlineUsers());

            foreach ($onlyOnlines as $value) {
                $unreadPosts = UnreadChatPost::where(['room_id' => $room['id'], 'user_id' => $value])->first();
                if (!$unreadPosts) {
                    UnreadChatPost::create(['room_id' => $room['id'], 'user_id' => $value]);
                } else {
                    $unreadPosts->unread++;
                    $unreadPosts->update();
                }
                broadcast(new Post([
                    'toId' => $value,
                    'fromId' => $user->id,
                    'data' => $chatPost,
                ]))->toOthers();

            }
        }

        RoomUsers::where(['user_id' => $user->id])->delete();

        $action ? setcookie('userSession', '', time() - 3600, '/') : null;
        return response()->json(['success' => true], Response::HTTP_OK);
    }

    public function singleAuth($auth_type, $auth_key)
    {
        $encrypt_auth_key = "Aj190mfqjw9fajf";

        $member_info = explode(":", encryption::decode($auth_key, $encrypt_auth_key));

        if (isset($member_info[1])) {

            $user = User::where(['email' => $member_info[0], 'password' => $member_info[1]])->first();
            if ($user) {
                if ($auth_type == "signin") {
                    Auth::loginUsingId($user->id);
                    return response()->json(['success' => true, 'token' => $user->createToken('Token Name')->accessToken], Response::HTTP_OK);
                } elseif ($auth_type == "signout") {
                    return response()->json(['success' => true, 'token' => ''], Response::HTTP_OK);
                }
            }
            return response()->json(['success' => true, 'token' => ''], Response::HTTP_OK);
        }
        return response()->json(['success' => true, 'token' => ''], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *         path="/auth/register",
     *         tags={"Authentication"},
     *         summary="Register",
     *         description="Register a new user and send notification mail",
     *         operationId="register",
     *         @OA\Response( response=200,description="Successful registration" ),
     *         @OA\Response( response=400, description="Invalid input or email taken( Validation errors )" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                      type="object",
     *                      required={"country","chamber_of_commerce","first_name","last_name","email","password"},
     *                      @OA\Property( property="country", description="Company country", type="integer", example="1" ),
     *                      @OA\Property( property="chamber_of_commerce", description="Company chamber of commerce", type="string", example="125987545858" ),
     *                      @OA\Property( property="first_name", description="User first name", type="string" ,example="John"),
     *                      @OA\Property( property="last_name", description="User last name", type="string", example="Smith" ),
     *                      @OA\Property( property="email", description="Email", type="string" , example="johnsmith@mail.com"),
     *                      @OA\Property( property="password", description="Password", type="string", format="password", example="secret" ),
     *                      @OA\Property( property="recaptcha", description="Recaptcha", type="string", example="03AOLTBLTPHelBjZqZMamTkr6-kqQ-FcGtg2CfFwPkFiI8ERi454f8GZ46M0p7_eEif6yCCN2GxPhpJ..." )
     *                 )
     *             )
     *         )
     * )
     */
    public function register(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'country' => 'required|integer|exists:country,id',
            'chamber_of_commerce' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string',
            'recaptcha' => ['required', new Recaptcha],
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $company = Company::create(['country_id' => $request->get('country'), 'chamber_of_commerce' => $request->get('chamber_of_commerce')]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['activation_token'] = Str::random(60);
        $input['company_id'] = $company->id;
        $user = User::create($input);
        $user->assignRole('manager');

        // Send email with activation link
        $user->notify(new RegisterActivate($user));

        return response()->json(['success' => true], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *         path="/auth/register/activate/{token}",
     *         tags={"Authentication"},
     *         summary="Activate user",
     *         description="Activate an registered user",
     *         operationId="activateUser",
     *         @OA\Parameter(
     *             name="token",
     *             in="path",
     *             description="User activating token (should be included in the verification mail)",
     *             required=true,
     *             @OA\Schema( type="string")
     *         ),
     *         @OA\Response( response=200, description="Successful operation" ),
     *         @OA\Response( response=400, description="Invalid token" ),
     *         @OA\Response( response=500, description="Server error" ),
     * )
     */
    public function activate($token)
    {
        $user = User::where('activation_token', $token)->first();
        // If the token is not existing, throw error
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'This activation token is invalid'], Response::HTTP_BAD_REQUEST);
        }
        // Update activation info
        $user->active = true;
        $user->activation_token = '';
        $user->email_verified_at = Carbon::now();
        $user->save();
        return response()->json(['success' => true, 'data' => $user], Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *         path="/auth/password/token/find/{token}",
     *         tags={"Authentication"},
     *         summary="Verify reset password token",
     *         description="Verify the reset password token and make sure it is existing and still valid",
     *         operationId="findPasswordResetToken",
     *         @OA\Parameter(
     *             name="token",
     *             in="path",
     *             description="Password reset token (should be included in the notification mail)",
     *             required=true,
     *             @OA\Schema(type="string")
     *         ),
     *         @OA\Response( response=200, description="Successful" ),
     *         @OA\Response( response=400, description="Invalid token" ),
     *         @OA\Response( response=500, description="Server error" )
     * )
     */

    public function findPasswordResetToken($token)
    {
        // Make sure the password reset token is findable, otherwise throw error
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset) {
            return response()->json(['success' => false, 'message' => "This password reset token is invalid"], Response::HTTP_BAD_REQUEST);
        }
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json(['success' => true, 'message' => "This password reset token is invalid"], Response::HTTP_BAD_REQUEST);
        }
        return response()->json(['success' => true, 'data' => $passwordReset], Response::HTTP_OK);
    }


    /**
     * @OA\Post(
     *         path="/auth/password/token/create",
     *         tags={"Authentication"},
     *         summary="Request resetting password",
     *         description="Generate password reset token and send that token to user through mail",
     *         operationId="createPasswordResetToken",
     *         @OA\Response( response=200, description="Successful" ),
     *         @OA\Response( response=404, description="Email not existing" ),
     *         @OA\Response( response=400, description="Invalid input" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                     type="object",
     *                      required={"email"},
     *                     @OA\Property(
     *                         property="email",
     *                         description="Email",
     *                         type="string",
     *                     ),
     *                 )
     *             )
     *         )
     * )
     */

    public function createPasswordResetToken(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        $user = User::where('email', $request->email)->first();
        // If the email is not existing, throw error
        if (!$user) {
            return response()->json(['success' => false, 'message' => "We can't find a user with that e-mail address"], Response::HTTP_BAD_REQUEST);
        }
        // Create or update token
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60)
            ]
        );
        if ($user && $passwordReset) {
            $user->notify(new PasswordResetRequest($passwordReset->token));
        }
        return response()->json(['success' => true, 'message' => "We have e-mailed your password reset link"], Response::HTTP_OK);
    }


    /**
     * @OA\Post(
     *         path="/auth/password/reset",
     *         tags={"Authentication"},
     *         summary="Reset password",
     *         description="Set new password for the user",
     *         operationId="resetPassword",
     *         @OA\Response( response=200, description="Successful operation" ),
     *         @OA\Response( response=404, description="Password reset token invalid or email not existing" ),
     *         @OA\Response( response=400, description="Invalid input"),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"email","password","token"},
     *                     @OA\Property( property="email", description="Email", type="string" ),
     *                     @OA\Property( property="password", description="Password", type="string", format="password" ),
     *                     @OA\Property( property="token", description="Password reset token", type="string" ),
     *                 )
     *             )
     *         )
     * )
     */

    public function resetPassword(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'token' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();
        if (!$passwordReset) {
            return response()->json(['success' => false, 'message' => "Invalid input data"], Response::HTTP_NOT_FOUND);
        }
        $user = User::where('email', $passwordReset->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => "We can't find a user with that e-mail address"], Response::HTTP_NOT_FOUND);
        }
        // Save new password
        $user->password = Hash::make($request->password);
        $user->save();
        // Delete password reset token
        $passwordReset->delete();

        return response()->json(['success' => true, 'data' => $user], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *         path="/auth/refresh",
     *         tags={"Authentication"},
     *         summary="Refresh token",
     *         description="Refresh an user's token",
     *         operationId="refreshToken",
     *         @OA\Response( response=200, description="Successful refresh" ),
     *         @OA\Response( response=400, description="Invalid input" ),
     *         @OA\Response( response=404, description="Wrong combination of email and password or email not verified" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\Response( response=401, description="UNAUTHORIZED" ),
     *          security={ {"bearerAuth": {}}},
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                      type="object",
     *                      required={"refresh_token"},
     *                      @OA\Property( property="refresh_token", description="Client refresh token", type="string"),
     *                 )
     *             )
     *         )
     * )
     */

    public function refreshToken(Request $request)
    {
        $response = $this->refreshUserToken($request->get('refresh_token'));
        if (!$response) {
            return response()->json(['success' => false, 'message' => "Invalid refresh token"], Response::HTTP_NOT_FOUND);
        }
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        // Save new password

        return response()->json(['success' => true, 'token' => $access_token, 'refresh_token' => $refresh_token], Response::HTTP_OK);
    }

    private function refreshUserToken($refresh_token)
    {
        $grantClient = DB::table('oauth_clients')->find(2);
        $http = new Client();
        $url = env('APP_URL') . '/oauth/token';
        $response = $http->post($url, [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token,
                'client_id' => $grantClient->id,
                'client_secret' => $grantClient->secret,
                'scope' => '*',
            ], 'http_errors' => false
        ]);


        if ($response->getStatusCode() == 200) {
            return json_decode((string)$response->getBody(), false);
        } else
            return;

    }

    private function makeUserToken($email, $password)
    {
        $grantClient = DB::table('oauth_clients')->whereId(2)->first();
        $http = new Client();
        $url = env('APP_URL') . '/oauth/token';

        $response = $http->post($url, [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => (string)$grantClient->id,
                'client_secret' => $grantClient->secret,
                'username' => $email,
                'password' => $password,
                'scope' => '*',
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            return json_decode((string)$response->getBody(), false);
        } else
            return;
    }

}
