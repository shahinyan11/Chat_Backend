<?php

namespace App\Http\Controllers\Api;

use App\Events\Like;
use App\Events\PrivateMessageSent;
use App\Events\Post;
use App\Events\ChatRoomEvent;
use App\Events\PostCommentSent;
use App\Models\ChatPostVotes;
use App\Models\ChatAttachmentVotes;
use App\Models\RoleUser;
use App\Models\RoomBans;
use App\Models\UnreadChatPost;
use App\Models\UserKicks;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
use App\Helpers\Data;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Pusher\Pusher;
use Symfony\Component\HttpFoundation\Response as Response;
use App\Models\ChatRoom;
use App\Models\ChatPost;
use App\Models\ChatAttachment;
use App\Models\RoomUsers;
use App\Models\UserImage;
use App\Models\ChatPostReply;
use App\Models\Reports;
use Validator;
use File;
use Intervention\Image\ImageManagerStatic as Image;
use DB;

class   ChatController extends ApiController
{

    /**
     * @OA\Post(
     *         path="/message",
     *         tags={"Chat"},
     *         summary="send message",
     *         description="send message",
     *         operationId="sendMessage",
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
     *                      @OA\Property( property="userId", description="id", type="string" ),
     *                      @OA\Property( property="message", description="Message", type="string" ),
     *                 )
     *             )
     *         )
     * )
     */
    public function message($data)
    {
        broadcast(new PrivateMessageSent($data))->toOthers();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @OA\Post(
     *         path="/chat/room",
     *         tags={"Chat"},
     *         summary="Create chat room",
     *         description="Create chat room",
     *         operationId="create_chat",
     *         @OA\Response( response=200,description="Successful operation" ),
     *         @OA\Response( response=400, description="Validation errors" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                      type="object",
     *                      required={"name","privacy","chamber_of_commerce"},
     *                      @OA\Property( property="name", description="Room name", type="string", example="room1" ),
     *                      @OA\Property( property="description", description="Room rules", type="text", example="rules.... "),
     *                      @OA\Property( property="privacy",   description="Room privacy", type="text", example="public"),
     *                      @OA\Property( property="password", description="Room passwort", type="string" , example="37494921295")
     *                 )
     *             )
     *         ),
     *          security={ {"bearerAuth": {} }}
     * )
     */

    public function createRoom(Request $request)
    {
        $user = Auth::user();

        $request->request->add([
            'owner_id' => $user->id,
            'retention' => 24,
            'privacy' => $request->privacy ? $request->privacy : 'user'
        ]);

        // Validate input data
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:chat_room,name',
            'owner_id' => 'required',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        if ($request->privacy == 'user') {
            $userRoom = ChatRoom::where([
                'owner_id' => $user->id,
                'privacy' => 'user'
            ])->count();
            if (!$userRoom) {
                $room = ChatRoom::create($request->all());

                $owner = User::find($room->owner_id);
                $room->owner_avatar = $owner ? $owner->avatar : '';
                $room->owner_screen_name = $owner ? $owner->screen_name : '';

                if ($room->id) {
                    RoomUsers::firstOrCreate([
                        'user_id' => $user->id,
                        'chat_room_id' => $room->id
                    ]);
                }
                $chanelUsers = Data::getOnlineUsers();

                foreach ($chanelUsers as $value) {
                    if ($value != $user->id) {
                        broadcast(new ChatRoomEvent([
                            'toId' => $value,
                            'data' => $room
                        ]))->toOthers();
                    }
                }
                $room['joinStatus'] = 1;
                return response()->json(['success' => true, 'data' => $room], Response::HTTP_OK);
            } else {
                return response()->json(['success' => false, 'errors' => 'Each user can create only one room'], Response::HTTP_BAD_REQUEST);
            }
        }

        $room = ChatRoom::create($request->all());

//        $owner = User::find($room->owner_id);
//        $room->owner_avatar = $owner ? $owner->avatar:'';
//        $room->owner_screen_name = $owner?$owner->screen_name:'';

        RoomUsers::firstOrCreate([
            'user_id' => $user->id,
            'chat_room_id' => $room->id
        ]);
        $chanelUsers = Data::getOnlineUsers();

        foreach ($chanelUsers as $value) {
            if ($value != $user->id) {
                broadcast(new ChatRoomEvent([
                    'toId' => $value,
                    'data' => $room,
                    'action' => 'add'
                ]))->toOthers();
            }
        }
        return response()->json(['success' => true, 'data' => $room], Response::HTTP_OK);
    }

    public function downloadImage($path)
    {

        return response()->download('uploads/' . $path, $path);

        // Process download
        if (file_exists(public_path() . '/' . $filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            flush(); // Flush system output buffer
            readfile($filepath);
            exit;
        }
    }

    public function deleteRoom(ChatRoom $room)
    {
        $user = Auth::user();
        $deleted = $room->delete();

        if ($deleted) {
            $chanelUsers = Data::getOnlineUsers();

            foreach ($chanelUsers as $value) {
                if ($value != $user->id) {
                    broadcast(new ChatRoomEvent([
                        'toId' => $value,
                        'action' => 'delete',
                        'data' => [
                            'id' => $room->id,
                            'success' => $deleted,
                        ]
                    ]))->toOthers();
                }
            }

        }
        return response()->json([
            'data' => [
                'id' => $room->id,
                'success' => $deleted,
            ]
        ], Response::HTTP_OK);
    }

    public function updateRoom(Request $request)
    {
        $roomId = $request->id;
        $request->request->remove('id');
        $request->request->remove('_method');

        $room = ChatRoom::where(['id' => $roomId])->first();
        $room->update($request->all());


        return response()->json([
            'success' => true,
            'room' => $room
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @OA\Post(
     *         path="/chat/roomJoin",
     *         tags={"Chat"},
     *         summary="room joined ",
     *         description="room joined ",
     *         operationId="room_joined",
     *         @OA\Response( response=200,description="Successful operation" ),
     *         @OA\Response( response=400, description="Validation errors" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                      type="object",
     *                      required={"user_id","chat_room_id"},
     *                      @OA\Property( property="user_id", description="User id", type="int", example="1" ),
     *                      @OA\Property( property="chat_room_id", description="Room idd", type="int" ,example="10"),
     *                 )
     *             )
     *         ),
     *          security={ {"bearerAuth": {} }}
     * )
     */

    public function roomJoin(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        $roomId = $data['activeRoomId'];
        $room = ChatRoom::where([
            'id' => $roomId,
            'password' => $data['password']
        ])->get();
        $userKiks = UserKicks::where([
            'user_id'=> $user->id,
            'chat_room_id' => $roomId,
        ])->first();
        $diffMinutes = $userKiks ? Carbon::now()->diffInMinutes($userKiks->updated_at) : null;
        $kickPeriod = $userKiks ?  $userKiks->kick_count * 15 : null;
        if ($room->count()) {
           if (!$userKiks || $diffMinutes >= $kickPeriod){
               $roomUsers = RoomUsers::firstOrCreate([
                   'user_id' => $user->id,
                   'chat_room_id' => $roomId
               ]);
               $chatPost = ChatPost::create([
                   'chat_room_id' => $roomId,
                   'owner_id' => $user->id,
                   'type' => 'room_join'
               ]);

               $chatPost['user_info'] = $user;

               $onlyOnlines = Data::getOnlineUsers();

               foreach ($onlyOnlines as $value) {
//                $unreadPosts = UnreadChatPost::where(['room_id' => $roomId, 'user_id' => $value])->first();
//                if (!$unreadPosts) {
//                    UnreadChatPost::create(['room_id' => $roomId, 'user_id' => $value]);
//                } else {
//                    $unreadPosts->unread++;
//                    $unreadPosts->update();
//                }
                   broadcast(new Post([
                       'toId' => $value,
                       'fromId' => $user->id,
                       'data' => $chatPost,
                   ]))->toOthers();

               }
               return response()->json([
                   'success' => true,
                   'roomUsers' => $roomUsers,
                   'post' => $chatPost
               ]);
           }else{
               return response()->json(['success' => false, 'error' => 'You can join through ' . ($kickPeriod - $diffMinutes) . ' minutes'], Response::HTTP_BAD_REQUEST);
           }

        }

        return response()->json(['success' => false, 'message' => 'wrong password'], Response::HTTP_NOT_FOUND);
    }

    public function roomLeave(Request $request)
    {
        $user = Auth::user();

        $chatRoom = ChatRoom::where('id', $request['roomId'])->first();

        $chatPost = ChatPost::create([
            'chat_room_id' => $chatRoom['id'],
            'owner_id' => $user->id,
            'type' => 'room_left'
        ]);

        $chatPost['user_info'] = $user;


        $onlyOnlines = Data::getOnlineUsers();

        foreach ($onlyOnlines as $value) {
//                $unreadPosts = UnreadChatPost::where(['room_id' => $chatRoom['id'], 'user_id' => $value])->first();
//                if (!$unreadPosts) {
//                    UnreadChatPost::create(['room_id' => $chatRoom['id'], 'user_id' => $value]);
//                } else {
//                    $unreadPosts->unread++;
//                    $unreadPosts->update();
//                }
            broadcast(new Post([
                'toId' => $value,
                'fromId' => $user->id,
                'data' => $chatPost,
            ]))->toOthers();

        }

        RoomUsers::where(['user_id' => $user->id])->delete();

        return response()->json(['success' => true, 'roomId' => $request['roomId']], Response::HTTP_OK);
    }

    public function getRooms(Request $request)
    {
        $user = Auth::user();

        $roleUser = RoleUser::where(['user_id' => $user->id])->value('role');
        $moderator = $roleUser === 'moderator' ? 1 : 0;

        $roomSql = "SELECT id, name, description, post_count, report_count, privacy, retention,owner_id,IF(password IS NULL,0,1) as password,
                             IF( (SELECT id FROM room_bans WHERE chat_room.id = room_bans.chat_room_id AND room_bans.user_id=:id),1,0) as baned,
                            (SELECT unread FROM unread_chat_post WHERE room_id = chat_room.id AND unread_chat_post.user_id=:user) as unread, 
                            IF( (SELECT user_id FROM room_users WHERE chat_room.id = room_users.chat_room_id AND room_users.user_id=:user_id),1,IF(owner_id=:owner_id or $moderator = 1, 1, 0)) as joinStatus,
                            (SELECT count(*) FROM room_users WHERE chat_room.id = room_users.chat_room_id) as roomUserCount FROM `chat_room`";

        $rooms = DB::select(DB::raw($roomSql), array(
            'user' => $user->id,
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'id' => $user->id,
        ));


        foreach ($rooms as $room) {
            $owner = User::find($room->owner_id);
            $room->owner_avatar = $owner ? $owner->avatar : '';
            $room->owner_screen_name = $owner ? $owner->screen_name : '';
        }


        return response()->json([
            'rooms' => $rooms
        ]);
    }

    public function getUserRoom(Request $request)
    {

        $id = $request->id ? $request->id : $request->user_id;
        $room = ChatRoom::where([
            'owner_id' => $id,
            'privacy' => 'user'
        ])->get();

        return response()->json($room);
    }

    public function getPosts(Request $request)
    {
        $user = Auth::user();

        $roomId = $request->roomId;

        $posts = ChatPost::with([
            'userInfo',
            'report',
            'chatAttachment' => function ($q) use ($user) {
                $q->with([
                    'userImage',
                    'report',
                    'chatAttachmentVotes' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    },
                    'chatPost' => function ($query) {
                        $query->with('userInfo');
                    }
                ]);
            },
            'chatPostReply' => function ($q) {
                $q->with('user');
            },
            'chatPostVotes' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }
        ])->where('chat_room_id', $roomId)->orderBy('id', 'DESC')->paginate(25);

        $unreadPosts = UnreadChatPost::where(['room_id' => $roomId, 'user_id' => $user->id])->first();
        if ($unreadPosts) {
            if ($unreadPosts->unread > 10) {
                $unreadPosts->unread -= 10;
            } else {
                $unreadPosts->unread = 0;
            }
            $unreadPosts->update();
        }


        return response()->json($posts);
    }

    public function getPostReplys(Request $request)
    {
//        $replys = ChatPostReply::with('user')->where('chat_post_id', $request->postId)->orderBy('id', 'DESC')->paginate(10);
//
//        return response()->json($replys);
    }

    public function getRoomOnlineUsers(ChatRoom $room)
    {

        $chanelUsers = Data::getOnlineUsers();
        $roomOnlineUsers = RoomUsers::where('chat_room_id', $room->id)->whereIn('user_id', $chanelUsers)->pluck('user_id')->toArray();

        $users = [];

        if (!empty($roomOnlineUsers)) {
            $users = User::whereIn('id', $roomOnlineUsers)->get();
        }
        $roomUsers = RoomUsers::where('chat_room_id', $room->id)->pluck('user_id')->toArray();
        return response()->json([
            'roomUsers' => $roomUsers,
            'roomOnlineUsers' => $users
        ]);
    }

    public function getPostedPhotos(Request $request)
    {

        $roomId = $request->roomId;
        $offset = $request->offset;
//        $posts = ChatAttachment::with(['userImage'])->where('chat_room_id', $roomId)->orderBy('id', 'desc')->paginate(10);
        $attachments = ChatAttachment::with([
            'userImage',
            'chatPost' => function ($query) {
                $query->with('userInfo');
            }
        ])->where('chat_room_id', $roomId)->orderBy('id', 'desc');
        $count = $attachments->count();
        $res = $attachments->offset($offset)->limit(20)->get();
        return response()->json([
            'count' => $count,
            'offset' => $offset,
            'attachments' => $res,
        ]);

    }

    public function makeComment(Request $request)
    {


        $id = ChatPostReply::create([
            'chat_post_id' => $request['postId'],
            'owner_id' => $request['userId'],
            'body' => $request['comment'],
            'report_count' => 0
        ])->id;

        $postReply = ChatPostReply::with('user', 'chatPost')->where('id', $id)->get();

        $users = RoomUsers::where('chat_room_id', $request['roomId'])->get('user_id')->toArray();

        foreach ($users as $value) {
            if ($value['user_id'] != $request['userId']) {
                broadcast(new PostCommentSent([
                    'toId' => $value['user_id'],
                    'fromId' => $request['userId'],
                    'data' => [
                        'roomId' => $request['roomId'],
                        'postReply' => $postReply[0]
                    ]
                ]))->toOthers();
            }
        }

        return response()->json([
            'roomId' => $request['roomId'],
            'postReply' => $postReply[0]
        ]);
    }

    public function removeAttachment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $user = Auth::user();
        $roleUser = RoleUser::where(['user_id' => $user->id])->value('role');
        $deleted = false;
        if ($roleUser === 'moderator') {
            $deleted = ChatAttachment::where($request->all())->delete();
        } else {
            $deleted = ChatAttachment::where([
                'id' => $request->id,
                'owner_id' => $user->id
            ])->delete();
        }

        if ($deleted) {
            return response()->json(['success' => $deleted], Response::HTTP_OK);
        } else {
            return response()->json(['success' => false, 'error' => 'you are not allow delete this attachment'], Response::HTTP_BAD_REQUEST);
        }

    }

    public function removeComment(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        $deleted = ChatPostReply::where($request->all())->delete();

        return response()->json(['success' => $deleted], Response::HTTP_OK);
    }

    public function removePost(ChatPost $post)
    {
        $user = Auth::user();
        $users = RoomUsers::where('chat_room_id', $post->chat_room_id)->get('user_id')->toArray();

        $roleUser = RoleUser::where(['user_id' => $user->id])->value('role');
        $deleted = false;
        if ($roleUser === 'moderator' || $post->owner_id === $user->id) {
            $deleted = $post->delete();
        }

        if ($deleted) {
            foreach ($users as $value) {
                if ($value['user_id'] != $user->id) {
                    broadcast(new Post([
                        'toId' => $value['user_id'],
                        'success' => $deleted,
                        'delete' => true,
                        'data' => [
                            'id' => $post->id,
                            'chat_room_id' => $post->chat_room_id
                        ],
                    ]))->toOthers();
                }
            }
            return response()->json([
                'success' => $deleted,
                'data' => [
                    'id' => $post->id,
                    'chat_room_id' => $post->chat_room_id
                ]
            ], Response::HTTP_OK);
        };
        return response()->json(['success' => false, 'error' => 'you are not allow delete this post'], Response::HTTP_BAD_REQUEST);
    }

    public function makePost(Request $request)
    {
        ini_set('memory_limit', '1000M');
        $validator = Validator::make($request->all(), [
            'message' => 'required_without:file',
            'file' => 'required_without:message'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        $images = $request->file('file');
        $user = Auth::user();
        $attachment = !empty($images) ? true : false;
        $sourceId = null;
        $roomId = $request['roomId'];
        $chatPost = ChatPost::create([
            'chat_room_id' => $roomId,
            'owner_id' => $user->id,
            'body' => $request['message'],
            'reply_to' => $request['replyTo'],
            'attachment' => $attachment,
        ]);

        if (!empty($images)) {
            $path = public_path() . '/uploads';
            if (!file_exists($path)) {
                File::makeDirectory($path, $mode = 0777, true, true);
            }
            foreach ($images as $image) {
                $data = Data::resizeImage($image);
                $userImage = UserImage::create([
                    'owner_id' => $user->id,
                    'property_type' => 'chat',
                    'property_id' => '2',
                    'privacy' => 'public',
                    'src' => $data['originalPath'],
                    'width' => Image::make($data['image_400'])->width(),
                    'height' => Image::make($data['image_400'])->height()
                ]);
                ChatAttachment::create([
                    'owner_id' => $user->id,
                    'type' => 'image',
                    'src_id' => $userImage->id,
                    'chat_post_id' => $chatPost->id,
                    'chat_room_id' => $roomId
                ]);
            }
        }

        $users = RoomUsers::where('chat_room_id', $roomId)->pluck('user_id')->toArray();
        $onlyOnlines = array_intersect($users, Data::getOnlineUsers());

        $post = ChatPost::with([
            'userInfo',
            'chatAttachment' => function ($q) use ($user) {
                $q->with([
                    'userImage',
                    'chatAttachmentVotes' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    },
                    'chatPost' => function ($query) {
                        $query->with('userInfo');
                    }
                ]);
            },
            'chatPostReply' => function ($q) {
                $q->with('user');
            },
            'chatPostVotes' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }
        ])->where('id', $chatPost->id)->first();

        foreach ($onlyOnlines as $value) {
            $unreadPosts = UnreadChatPost::where(['room_id' => $roomId, 'user_id' => $value])->first();
            if (!$unreadPosts) {
                UnreadChatPost::create(['room_id' => $roomId, 'user_id' => $value]);
            } else {
                $unreadPosts->unread++;
                $unreadPosts->update();
            }
            broadcast(new Post([
                'toId' => $value,
                'fromId' => $user->id,
                'data' => $post,
            ]))->toOthers();

        }
        return response()->json($post);

    }

    public function report(Request $request)
    {
        $user = Auth::user();
        $request->request->add([
            'reported_by' => $user->id,
        ]);

        $report = Reports::where($request->all())->first();
        if ($report) {
            if ($report->delete()) {
                $report = false;
            };
        } else {
            $report = Reports::create($request->all());
        }


        return response()->json(['success' => true, 'data' => $report], Response::HTTP_OK);
    }

    /**
     * @OA\GET(
     *         path="/vote-image/{id}/{like}",
     *         tags={"Chat"},
     *         summary="vote chat image",
     *         description="Like or dislike image",
     *         operationId="voteChatImage",
     *         @OA\Response( response=200,description="Voted" ),
     *         @OA\Response( response=400, description="Invalid input (Validation errors )" ),
     *         @OA\Response( response=404,description="Attachment doesn't exists" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\Parameter(
     *             name="id",
     *             in="path",
     *             description="Chat attachment id",
     *             required=true,
     *             @OA\Schema(type="string")
     *         ),
     *        @OA\Parameter(
     *             name="like",
     *             in="path",
     *             description="Vote(like=1 and dislike=0)",
     *             required=true,
     *             @OA\Schema(type="integer")
     *         ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function voteImage($id)
    {
        $user = Auth::user();

        $chatAttachment = ChatAttachment::find($id);
        if (!$chatAttachment) {
            return response()->json(['success' => false, 'message' => "Attachment doesn't exists"], Response::HTTP_OK);
        }

        $chatAttchmentVote = ChatAttachmentVotes::where(['chat_attachment_id' => $id, 'user_id' => $user->id])->first();

        if (!$chatAttchmentVote) {
            ChatAttachmentVotes::create([
                'chat_attachment_id' => $id,
                'user_id' => $user->id,
                'like' => 1
            ]);
        } else {
            $chatAttchmentVote->delete();
        }

        $likesCount = ChatAttachmentVotes::where(['chat_attachment_id' => $id])->count();
        $chatAttachment->score = $likesCount;

        $chatAttachment->update();

        $users = RoomUsers::where('chat_room_id', $chatAttachment->chat_room_id)->pluck('user_id')->toArray();
        $onlyOnlines = array_intersect($users, Data::getOnlineUsers());

        foreach ($onlyOnlines as $value) {

            broadcast(new Like([
                'toId' => $value,
                'fromId' => $user->id,
                'attachmentId' => $chatAttachment->id,
                'roomId' => $chatAttachment->chat_room_id,
                'postId' => $chatAttachment->chat_post_id,
                'data' => [
                    'score' => $likesCount,
                ]

            ]))->toOthers();

        }
        return response()->json([
            'success' => true,
            'score' => $likesCount,
            'like' => !$chatAttchmentVote,
        ], Response::HTTP_OK);
    }

    /**
     * @OA\GET(
     *         path="/kickUser",
     *         tags={"Chat"},
     *         summary="kick user from room",
     *         description="kick user from room",
     *         operationId="roomKickUser",
     *         @OA\Response( response=200,description="kicked" ),
     *         @OA\Response( response=400, description="Invalid input (Validation errors )" ),
     *         @OA\Response( response=404,description="Attachment doesn't exists" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                      type="object",
     *                      required={"user_id","chat_room_id"},
     *                      @OA\Property( property="user_id", description="User id", type="int", example="1" ),
     *                      @OA\Property( property="chat_room_id", description="Room idd", type="int" ,example="10"),
     *                 )
     *             )
     *         ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function kickUser(Request $request)
    {
        $user = Auth::user();

        RoomUsers::where($request->all())->delete();
        $userKick = UserKicks::firstOrCreate($request->all());
        $userKick->kick_count += 1;
        $userKick->save();

        broadcast(new ChatRoomEvent([
            'toId' => $request->user_id,
            'fromId' => $user->id,
            'roomId' => $request->chat_room_id,
            'action' => 'kick',
        ]))->toOthers();

        return response()->json(['success' => true,], Response::HTTP_OK);
    }

    public function banUser(Request $request)
    {
        $user = Auth::user();

        $deleted = RoomUsers::where($request->all())->delete();
        RoomBans::firstOrCreate($request->all());

        broadcast(new ChatRoomEvent([
            'toId' => $request->user_id,
            'fromId' => $user->id,
            'roomId' => $request->chat_room_id,
            'action' => 'ban',
            'data' => [
                'success' => $deleted,
                'id' => $request->chat_room_id
            ]

        ]))->toOthers();
        return response()->json(['success' => true,], Response::HTTP_OK);
    }

    /**
     * @OA\GET(
     *         path="/vote-post/{id}/{like}",
     *         tags={"Chat"},
     *         summary="vote chat post",
     *         description="Like or dislike post",
     *         operationId="voteChatPost",
     *         @OA\Response( response=200,description="Voted" ),
     *         @OA\Response( response=400, description="Invalid input (Validation errors )" ),
     *         @OA\Response( response=404,description="Post doesn't exists" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\Parameter(
     *             name="id",
     *             in="path",
     *             description="Chat post id",
     *             required=true,
     *             @OA\Schema(type="string")
     *         ),
     *        @OA\Parameter(
     *             name="like",
     *             in="path",
     *             description="Vote(like=1 and dislike=0)",
     *             required=true,
     *             @OA\Schema(type="integer")
     *         ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function votePost($id)
    {
        $user = Auth::user();

        $chatPost = ChatPost::find($id);
        if (!$chatPost) {
            return response()->json(['success' => false, 'message' => "Post doesn't exists"], Response::HTTP_OK);
        }

        $chatPostVote = ChatPostVotes::where(['chat_post_id' => $id, 'user_id' => $user->id])->first();

        if (!$chatPostVote) {
            ChatPostVotes::create([
                'chat_post_id' => $id,
                'user_id' => $user->id,
                'like' => 1
            ]);
        } else {
            $chatPostVote->delete();
        }

        $likesCount = ChatPostVotes::where(['chat_post_id' => $id])->count();

        $chatPost->score = $likesCount;

        $chatPost->update();

        $users = RoomUsers::where('chat_room_id', $chatPost->chat_room_id)->pluck('user_id')->toArray();
        $onlyOnlines = array_intersect($users, Data::getOnlineUsers());

        foreach ($onlyOnlines as $value) {

            broadcast(new Like([
                'toId' => $value,
                'fromId' => $user->id,
                'postId' => $chatPost->id,
                'roomId' => $chatPost->chat_room_id,
                'data' => [
                    'score' => $likesCount,
                ]

            ]))->toOthers();

        }

        return response()->json([
            'success' => true,
            'score' => $likesCount,
            'like' => !$chatPostVote,
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *         path="/chat-post/{id}",
     *         tags={"Chat"},
     *         summary="edit chat post",
     *         description="edit post",
     *         operationId="editChatPost",
     *         @OA\Response( response=200,description="Success edit" ),
     *         @OA\Response( response=400, description="Invalid input (Validation errors )" ),
     *         @OA\Response( response=404,description="Post doesn't exists" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\Parameter(
     *             name="id",
     *             in="path",
     *             description="Post id",
     *             required=true,
     *             @OA\Schema(type="string")
     *         ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                      required={"body"},
     *                      type="object",
     *                      @OA\Property( property="body", description="Post body", type="text" ),
     *                 )
     *             )
     *         ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function editChatPost($id, Request $request)
    {
        $user = Auth::user();

        $chatPost = ChatPost::find($id);
        if (!$chatPost) {
            return response()->json(['success' => false, 'message' => "Post doesn't exists"], Response::HTTP_OK);
        }

        $roleUser = RoleUser::where(['user_id' => $user->id])->value('role');

        if ($roleUser === 'moderator' || $chatPost->owner_id === $user->id) {
            $chatPost->body = $request->get('body');
            $chatPost->update();

            return response()->json(['success' => true, 'data' => $chatPost], Response::HTTP_OK);
        }

        return response()->json(['success' => false, 'error' => 'you are not allow edit this post'], Response::HTTP_BAD_REQUEST);


    }

    /**
     * @OA\Post(
     *         path="/chat-post-reply/{id}",
     *         tags={"Chat"},
     *         summary="edit chat post reply",
     *         description="edit post reply",
     *         operationId="editChatPostReply",
     *         @OA\Response( response=200,description="Success edit" ),
     *         @OA\Response( response=400, description="Invalid input (Validation errors )" ),
     *         @OA\Response( response=404,description="Post Reply doesn't exists" ),
     *         @OA\Response( response=500, description="Server error" ),
     *         @OA\Parameter(
     *             name="id",
     *             in="path",
     *             description="post reply id",
     *             required=true,
     *             @OA\Schema(type="integer")
     *         ),
     *         @OA\RequestBody(
     *             required=true,
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                      required={"body"},
     *                      type="object",
     *                      @OA\Property( property="body", description="Post reply  body", type="text" ),
     *                 )
     *             )
     *         ),
     *          security={ {"bearerAuth": {} }}
     * )
     */
    public function editChatPostReply($id, Request $request)
    {
        $user = Auth::user();

        $chatPostReply = ChatPostReply::find($id);
        if (!$chatPostReply) {
            return response()->json(['success' => false, 'message' => "Post reply doesn't exists"], Response::HTTP_OK);
        }
        $chatPostReply->body = $request->get('body');
        $chatPostReply->update();

        return response()->json(['success' => true], Response::HTTP_OK);
    }
}
