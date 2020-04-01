<?php

namespace App\Http\Controllers\Api;

use App\Events\PrivateMessageSent;
use App\Helpers\Data;
use App\Models\ChatAttachment;
use App\Models\ChatRoom;
use App\Models\UserImage;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\Response as Response;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Call;
use App\Models\ConversationParties;
use Validator;
use DB;
use File;
class ConversationController extends ApiController
{

    public $callerUser = null;
    public $toUserId = null;
    public $startCall = null;
    public $endCall = null;

    public function msToTime($duration)
    {

        $seconds = floor($duration % 3600 % 60);
        $minutes = floor($duration % 3600 / 60);
        $hours = floor($duration / 3600);

        $res = '';
        $res .= $hours > '0' ? $hours . "h " : '';
        $res .= $minutes > '0' ? $minutes . "m " : '';
        $res .= $seconds > '0' ? $seconds . "s" : '';

        return $res;
    }

    public function generateRoomName($user1, $user2)
    {
        if ($user1 < $user2) {
            return $user1 . '_' . $user2;
        }
        return $user2 . '_' . $user1;
    }

    public function getConversationId($author, $toUserId)
    {

        $conversationID = DB::table('conversation_parties as cp')
            ->leftJoin('conversation_parties as related', 'cp.conversation_id', '=', 'related.conversation_id')
            ->where(function ($query) use ($author,$toUserId){
                $query->where('cp.parties',$author)
                    ->where('related.parties',$toUserId);
            })->orWhere(function($query) use ($author,$toUserId){
                $query->where('cp.parties',$toUserId)
                    ->where('related.parties',$author);
            })->select('cp.conversation_id')->groupBy('cp.conversation_id')
            ->value('conversation_id');
        if (!$conversationID) {
            $conversationID = Conversation::create([
                'message_count' => '1'
            ])->id;
            ConversationParties::insert([
                ['conversation_id' => $conversationID, 'parties' => $author],
                ['conversation_id' => $conversationID, 'parties' => $toUserId]
            ]);
        }
        return $conversationID;
    }

//    public function conversationRoomMembers($author,$roomId)
//    {
//        if(!ConversationRoomMembers::where('user_id', $author['id'])->count()){
//            ConversationRoomMembers::create([
//                'user_id'   => $author['id'],
//                'room_id'   => $roomId,
//                'user_info' => $author
//            ]);
//        }
//    }

    public function call(Request $request)
    {
        $reqData = $request->all();
        $callId = null;
        if ($reqData['messageType'] === 'call' && $reqData['data']['type'] === 'offer') {

            $toUserId = $reqData['toId'];
            $callerUser = Auth::user();
            $conversationId = $this->getConversationId($callerUser['id'], $toUserId);

            $callId = Call::create([
                'dispositions' => 'no_answer',
            ])->id;
            $reqData['callId'] = $callId;
            $messageId = Message::create([
                'author_id' => $callerUser['id'],
                'body' => $request['message'],
                'conversation_id' => $conversationId,
                'attachment_id' => null,
                'call_id' => $callId,
                'user_info' => $callerUser
            ]);

        } else if ($reqData['messageType'] === 'call' && $reqData['data']['type'] === 'answer' && $reqData['access']) {

            $startCall = microtime(true);
            Call::where([
                'id' => $reqData['callId']
            ])->update([
                'start' => date('Y-m-d H:i:s', $startCall),
                'dispositions' => 'answered'
            ]);

        } else if ($reqData['messageType'] === 'change') {
            broadcast(new PrivateMessageSent($request->all()))->toOthers();
            return response()->json(['call_id' => $reqData['callId']]);
        }
        if (!$reqData['access']) {

            $duration = null;
            $callAauthorId = null;
            $endCall = microtime(true);
            $dispositions = '';
            if ($reqData['callId']) {
                $callAauthorId = Message::where([
                    'call_id' => $reqData['callId']
                ])->first()->author_id;
            };


            $call = Call::where([
                'id' => $reqData['callId']
            ])->first();
            $startCall = $call->start;
            if ($startCall !== null) {
                $duration = strtotime(date('Y-m-d H:i:s', $endCall)) - strtotime($startCall);
                $dispositions = 'answered';
            } else {
                $dispositions = $callAauthorId === $reqData['userId'] ? 'no_answer' : 'rejected';
            }
            $call->update([
                'end' => date('Y-m-d H:i:s', $endCall),
                'duration' => $this->msToTime($duration),
                'dispositions' => $dispositions
            ]);

            $newMessage = Message::with('messageAttachment', 'call')->where('call_id', $reqData['callId'])->first();
            $broadcastMessage = [
                'messageType' => 'text',
                'fromUserId' => $reqData['userId'],
                'toId' => $reqData['toId'],
                'data' => $newMessage
            ];
            broadcast(new PrivateMessageSent($reqData))->toOthers();
            broadcast(new PrivateMessageSent($broadcastMessage))->toOthers();
            return response()->json($newMessage);
        }
        broadcast(new PrivateMessageSent($reqData))->toOthers();
        return response()->json(['call_id' => $callId]);
    }


    public function sendMessage(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'message' => 'required_without:file',
            'file' => 'required_without:message'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        $broadcastMessage = [];
        $user = Auth::user();
        $userId = $user['id'];
        $toUserId = $request['userId'];
        $conversationId = $this->getConversationId($userId, $toUserId);
        $fileId = null;
        $callId = null;
        $filePath = null;
        $imagTypes = ['', 'png', 'jpeg', 'jpg', 'gif'];
        $files = $request->file('file');

        $messageId = Message::create([
            'author_id' => $userId,
            'body' => $request['message'],
            'conversation_id' => $conversationId,
            'call_id' => null,
        ])->id;

        if (!empty($files)) {
            $path = public_path() . '/uploads';
            if (!file_exists($path)) {
                File::makeDirectory($path, $mode = 0777, true, true);
            }
            foreach ($files as $file) {
                $fileType = array_search($file->extension(), $imagTypes) ? 'image' : 'video';
                $filePath = null;
                $data = [];
                if ($fileType == 'image') {
                    $data = Data::resizeImage($file);
                    $filePath = $data['originalPath'];
                }
                $userImageId = UserImage::create([
                    'owner_id' => $userId,
                    'property_type' => 'messenger',
                    'property_id' => '1',
                    'privacy' => 'member',
                    'src' => $filePath,
                    'width' => Image::make($data['image_400'])->width(),
                    'height' => Image::make($data['image_400'])->height()
                ])->id;

                MessageAttachment::create([
                    'type' => $fileType,
                    'src_id' => $userImageId,
                    'message_id' => $messageId
                ]);


            }
        }


        $newMessage = Message::with(
            [
                'userInfo',
                'messageAttachment' => function ($q) {
                    $q->with('userImage');
                },
                'call'
            ]

        )->where('id', $messageId)->first();

        $broadcastMessage = [
            'messageType' => 'text',
            'fromUserId' => $user['id'],
            'toId' => $request['userId'],
            'data' => $newMessage
        ];

        broadcast(new PrivateMessageSent($broadcastMessage))->toOthers();

        return response()->json($newMessage);
    }

    public function getMessages(Request $request)
    {
        $user = Auth::user();
        $selectedUserId = $request['userId'];
        $conversationId = $this->getConversationId($user->id, $selectedUserId);
        $data = [];
        $data = Message::with([
            'messageAttachment' => function($q){
                $q->with('userImage');
            },
            'call'
        ])->where('conversation_id', $conversationId)->get();

        return response()->json($data);

    }
}
