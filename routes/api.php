<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
$chatUrl = env('CHAT_URL');

if(version_compare(PHP_VERSION, '7.1', '>=')) {
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
}

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('login', ['as' => 'login', function () {
    return response()->json([
        "code" => 401,
        "message" => "Bad credentials( Please Login )"
    ], 401);
}]);
Route::get('downloadImage/{path}', 'Api\ChatController@downloadImage');

Route::group(['domain' => $chatUrl ], function()
{
    Route::get('webservices/user/{auth_type}/{auth_key}', 'Api\AuthController@singleAuth');
});

Route::group(['middleware' => ['guest:api']], function () {

    Route::prefix('auth')->group(function () {

        Route::post('login', 'Api\AuthController@login');
        Route::post('register', 'Api\AuthController@register');
        Route::get('register/activate/{token}', 'Api\AuthController@activate');

        Route::post('password/reset', 'Api\AuthController@resetPassword');
        Route::post('password/token/create', 'Api\AuthController@createPasswordResetToken');
        Route::get('password/token/find/{token}', 'Api\AuthController@findPasswordResetToken');
        Route::post('password/reset', 'Api\AuthController@resetPassword');

        Route::post('refresh', 'Api\AuthController@refreshToken');



    });

});


Route::group([ 'middleware' => 'auth:api' ], function () {

    Route::prefix('auth')->group(function () {

        Route::get('signOut', 'Api\AuthController@signOut');
        Route::get('closeWindow/{action}', 'Api\AuthController@signOut');
    });

    Route::post('sendMessage', 'Api\ConversationController@sendMessage');
    Route::post('getMessages', 'Api\ConversationController@getMessages');
    Route::post('videoChat', 'Api\ConversationController@call');

    Route::prefix('user')->group(function () {
        Route::get('list', 'Api\UserController@userList');
        Route::get('info', 'Api\UserController@getUserInfo');
        Route::post('password/change', 'Api\UserController@changePassword');
        Route::get('logout', 'Api\UserController@logout')->name('logout');
    });
    Route::prefix('chat')->group(function () {
        Route::group(['middleware' => 'throttle:60,1'], function(){
            Route::post('roomJoin', 'Api\ChatController@roomJoin');
            Route::post('roomLeave', 'Api\ChatController@roomLeave');
        });
        Route::post('post', 'Api\ChatController@makePost');
        Route::post('comment', 'Api\ChatController@makeComment');
        Route::post('removeComment', 'Api\ChatController@removeComment');
        Route::post('removeAttachment', 'Api\ChatController@removeAttachment');
        Route::delete('removePost/{post}', 'Api\ChatController@removePost');
        Route::get('getPosts', 'Api\ChatController@getPosts');
        Route::get('getPostedPhotos', 'Api\ChatController@getPostedPhotos');
        Route::post('room', 'Api\ChatController@createRoom');
        Route::delete('room/{room}', 'Api\ChatController@deleteRoom');
        Route::put('room', 'Api\ChatController@updateRoom');
        Route::post('report', 'Api\ChatController@report');
        Route::get('room', 'Api\ChatController@getRooms');
        Route::get('getRoomOnlineUsers/{room}', 'Api\ChatController@getRoomOnlineUsers');
        Route::get('getPostReplys', 'Api\ChatController@getPostReplys');
        Route::post('kickUser', 'Api\ChatController@kickUser');
        Route::post('banUser', 'Api\ChatController@banUser');
//        Route::get('downloadImage', 'Api\ChatController@downloadImage');
    });

    Route::get('vote-post/{id}', 'Api\ChatController@votePost');
    Route::get('vote-image/{id}', 'Api\ChatController@voteImage');
    Route::post('chat-post/{id}', 'Api\ChatController@editChatPost');
    Route::post('chat-post-reply/{id}', 'Api\ChatController@editChatPostReply');

    Route::prefix('forum')->group(function () {
        Route::get('list', 'Api\ForumController@list');
        Route::get('{id}/threads', 'Api\ForumController@threads');
    });

    Route::prefix('admin')->group(function () {
        Route::post('assign-role/{id}/{role}', 'Api\AdminController@asignRole');
        Route::get('roleUsers', 'Api\AdminController@roleUsers');
    });

    Route::prefix('thread')->group(function () {
        Route::get('{id}/posts', 'Api\ForumController@posts');
    });
});
