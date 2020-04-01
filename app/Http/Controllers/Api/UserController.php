<?php
namespace App\Http\Controllers\Api;

use App\Models\RoleUser;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Validator;


use Symfony\Component\HttpFoundation\Response as Response;

class UserController extends ApiController
{


    /**
     * @OA\Get(
     *         path="/user/logout",
     *         tags={"User"},
     *         summary="Logout",
     *         description="Logout an user",
     *         operationId="logout",
     *         @OA\Response( response=401, description="UNAUTHORIZED" ),
     *         @OA\Response( response=200, description="Successful loggout" ),
     *         @OA\Response( response=500, description="Server error" ),
     *          security={
     *           {"bearerAuth": {}}
     *       }
     * )
     */
    public function logout (Request $request) {
        $user = Auth::user();

        $token = $user->token();
        $token->revoke();

        return response()->json(['success' => true, 'message' => 'Successfully logged out'], Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *         path="/user/info",
     *         tags={"User"},
     *         summary="Get user info",
     *         description="Retrieve information from current user",
     *         operationId="getUser",
     *         @OA\Response( response=401, description="UNAUTHORIZED" ),
     *         @OA\Response( response=200, description="User info" ),
     *         @OA\Response( response=500, description="Server error" ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function getUserInfo()
    {
        $user = Auth::user();
        $roleUser = RoleUser::where(['user_id'=>$user->id])->value('role');
        $user->role = $roleUser;

        return response()->json(['success' => 'success', 'user' => $user ], Response::HTTP_OK);
    }


    /**
     * @OA\Post(
     *         path="/user/password/change",
     *         tags={"User"},
     *         summary="Change password",
     *         description="Change an user's password (requires current password) and send notification mail",
     *         operationId="changePassword",
     *         @OA\Response( response=200, description="Successful changed" ),
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
     *                      required={"password","new_password"},
     *                      @OA\Property( property="password", description="Current Password", type="string", format="password" ),
     *                      @OA\Property( property="new_password", description="New password", type="string", format="password" )
     *                 )
     *             )
     *         )
     * )
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        $email = $user->email;
        // Validate input data
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors'=>$validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        // Check if the combination of email and password is correct, if it is then proceed, if no, throw error
        $credentials = request(['password']);
        $credentials['email'] = $email;
        // Check the combination of email and password, also check for activation status
        if(!Auth::guard('web')->attempt($credentials)) {
            return response()->json(['success' => false, 'message' => 'Wrong password or email has not been verified'], Response::HTTP_NOT_FOUND);
        }
        // Save new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'data' => $user], Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *         path="/user/list",
     *         tags={"User"},
     *         summary="User list",
     *         description="User list",
     *         operationId="userList",
     *         @OA\Response( response=401, description="UNAUTHORIZED" ),
     *         @OA\Response( response=200, description="User list" ),
     *         @OA\Response( response=500, description="Server error" ),
     *          security={
     *           {"bearerAuth": {}}
     *       }
     * )
     */
    public function userList () {

        $user = Auth::user();

        $users = User::where('id','!=',$user->id)->take(20)->get();

        return response()->json(['success' => true, 'data'=>$users], Response::HTTP_OK);
    }

}
