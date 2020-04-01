<?php

namespace App\Http\Controllers\Api;

use App\Events\LikeImage;
use App\Events\PrivateMessageSent;
use App\Events\Post;
use App\Events\ChatRoomEvent;
use App\Events\PostCommentSent;

use App\Models\RoleUser;
use App\User;
use Illuminate\Http\Request;

use App\Helpers\Data;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as Response;


class   AdminController extends ApiController
{

    /**
     * @OA\Post(
     *         path="/admin/assign-role/{id}/{role}",
     *         tags={"Admin"},
     *         summary="Assign role to user",
     *         description="Assign role to user",
     *         operationId="assignRoleToAdmin",
     *         @OA\Response( response=200,description="Success assign" ),
     *         @OA\Response( response=400, description="Invalid input (Validation errors )" ),
     *         @OA\Response( response=404,description="Role doesn't exists" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\Parameter(
     *             name="id",
     *             in="path",
     *             description="user  id",
     *             required=true,
     *             @OA\Schema(type="integer")
     *         ),
     *         @OA\Parameter(
     *             name="role",
     *             in="path",
     *             description="role name",
     *             required=true,
     *             @OA\Schema(type="string")
     *         ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function asignRole($id, $role)
    {
        $user = Auth::user();

        if(!in_array($role,['admin','moderator'])){
            return response()->json(['success' => 'failed', 'message' => "Role doesn't exists"], Response::HTTP_BAD_REQUEST);
        }

        $userRole = RoleUser::where(['role'=>$role, 'user_id'=>$id])->first();
        if($userRole){
            return response()->json(['success' => 'failed', 'message' => 'User already have this role'], Response::HTTP_BAD_REQUEST);
        }

        $userRole = new RoleUser();
        $userRole->user_id  =$id;
        $userRole->role = $role;
        $userRole->save();

        $roleUser  = User::find($id);
        $roleUser->role = $role;

        return response()->json(['success' => true,'roleUser'=>$roleUser], Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *         path="/admin/roleUsers",
     *         tags={"Admin"},
     *         summary="Get users that have role",
     *         description="Roled users",
     *         operationId="roledUsers",
     *         @OA\Response( response=401, description="UNAUTHORIZED" ),
     *         @OA\Response( response=200, description="Users" ),
     *         @OA\Response( response=500, description="Server error" ),
     *          security={ {"bearerAuth": {} }}
     * )
     */

    public function roleUsers(){
        $data = [];
        $roleUsers = RoleUser::all();

        foreach ($roleUsers as $roleUser){
            $user  = User::find($roleUser->user_id);
            $user->role  = $roleUser->role;
            $data[] =   $user;
        }

        return response()->json(['success' => true,'data'=>$data], Response::HTTP_OK);
    }
}

