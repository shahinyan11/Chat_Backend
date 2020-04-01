<?php

namespace App\Helpers;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Pusher\Pusher;

class Data
{

    public static function resizeImage($image){
        $imagePath = Storage::disk('public_uploads')->putFile('/', $image);
        $image_resize_400 = Image::make($image->getRealPath());
        $image_resize_1024_700 = Image::make($image->getRealPath());
        $height = Image::make($image)->height();
        $width = Image::make($image)->width();

        if(Image::make($image_resize_1024_700)->width() > 1024){
            $image_resize_1024_700->resize(1024, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        if(Image::make($image_resize_1024_700)->height() > 700){
            $image_resize_1024_700->resize(null, 700, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $resizeImage = str_replace('.', '_1024_700.', $imagePath);
        $image_resize_1024_700->save(public_path('uploads/' . $resizeImage));

        if ($width >= $height && $width > 400) {
            $width = 400;
            $height = null;
        } else if ($width < $height && $height > 400) {
            $width = null;
            $height = 400;
        }
        $image_resize_400->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
        $resizeImage = str_replace('.', '_400.', $imagePath);
        $image_resize_400->save(public_path('uploads/' . $resizeImage));

        return [
            'originalPath' => $imagePath,
            'image_400' => $image_resize_400,
            'image_1024_700' => $image_resize_1024_700
        ];
    }

    public static function setCookie($name, $value, $expire_time = 0, $url = '/')
    {

        return setcookie($name, $value, time() + 60 * 60 * $expire_time, $url, false, false, true);

    }

    public static function getCookie($name)
    {

        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;

    }

    public static function deleteCookie($name)
    {

        setcookie($name, '', time(), '/');

    }

    public static function startSession()
    {

        session_start();

    }

    public static function setSession($name, $value)
    {

        $_SESSION[$name] = $value;

    }

    public static function deleteSession($name)
    {

        unset($_SESSION[$name]);

    }

    public static function getOnlineUsers()
    {

        $user = Auth::user();

        $connection = config('broadcasting.connections.pusher');

        $pusher = new Pusher(
            $connection['key'],
            $connection['secret'],
            $connection['app_id'],
            config('broadcasting.connections.pusher.options', [])
        );

        $channels = $pusher->get_users_info('presence-chat.presence');
        $chanelUsers = [];
        if ($channels && $channels->users) {
            foreach ($channels->users as $chanelUser) {
                $chanelUsers[] = $chanelUser->id;
            }
        }

        return array_diff($chanelUsers, [$user->id]);
    }
}
