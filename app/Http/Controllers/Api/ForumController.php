<?php

namespace App\Http\Controllers\Api;


use App\Models\Forum;
use App\Models\Post;
use App\Models\Thread;
use DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as Response;

class   ForumController extends ApiController
{

    /**
     * @OA\Get(
     *         path="/forum/list",
     *         tags={"Forum"},
     *         summary="Get forum list",
     *         description="Retrieve information from forum",
     *         operationId="forumList",
     *         @OA\Response( response=401, description="UNAUTHORIZED" ),
     *         @OA\Response( response=200, description="Forum list" ),
     *         @OA\Response( response=500, description="Server error" ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function list()
    {
        $user = Auth::user();
        $data = [];
        $forums = Forum::orderBy('parentid',"ASC")->get();

        foreach ($forums as $forum){
            if($forum->parentid == -1){
                $data[$forum->id] = $forum->toArray();
                $data[$forum->id]['data'] = [];
            }else{
                $data[$forum->parentid]['data'][] = $forum->toArray();
            }
        }
        return response()->json(['success' => 'success', 'data' => $data ], Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *         path="/forum/{id}/threads",
     *         tags={"Forum"},
     *         summary="Get forum threads list",
     *         description="Retrieve information from current forum threads",
     *         operationId="forumThreadsList",
     *         @OA\Parameter(
     *             name="id",
     *             in="path",
     *             description="Forum id",
     *             required=true,
     *             @OA\Schema(type="string")
     *         ),
     *         @OA\Response( response=401, description="UNAUTHORIZED" ),
     *         @OA\Response( response=200, description="Forum threads list" ),
     *         @OA\Response( response=500, description="Server error" ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function threads($id)
    {
        $user = Auth::user();

        $data = [];

        $threads = Thread::where('forum_id',$id)->get();

//        foreach ($forums as $forum){
//            if($forum->parentid == -1){
//                $data[$forum->id] = $forum->toArray();
//                $data[$forum->id]['data'] = [];
//            }else{
//                $data[$forum->parentid]['data'][] = $forum->toArray();
//            }
//        }
        return response()->json(['success' => 'success', 'data' => $threads ], Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *         path="/thread/{id}/posts",
     *         tags={"Forum"},
     *         summary="Get thread posts list",
     *         description="Retrieve information from current thread posts",
     *         operationId="threadPostsList",
     *         @OA\Parameter(
     *             name="id",
     *             in="path",
     *             description="Thread id",
     *             required=true,
     *             @OA\Schema(type="string")
     *         ),
     *         @OA\Response( response=401, description="UNAUTHORIZED" ),
     *         @OA\Response( response=200, description="Thread post list" ),
     *         @OA\Response( response=500, description="Server error" ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function posts($id)
    {
        $user = Auth::user();

        $data = [];

        $posts = Post::where('threadid',$id)->with('user')->paginate(10);


        return response()->json(['success' => 'success', 'data' => $posts ], Response::HTTP_OK);
    }
}

