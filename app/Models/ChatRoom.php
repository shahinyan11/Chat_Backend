<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChatRoom extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_room';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'owner_id', 'privacy', 'retention', 'description', 'post_count', 'report_count', 'password', 'setting'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public function chatPost()
    {
        return $this->hasMany('App\Models\CahtPost', 'chat_room_id', 'id');
    }

    public function userKick()
    {
        $user = Auth::user();
        return $this->hasOne('App\Models\UserKicks', 'chat_room_id', 'id')->where(['user_id' => $user->id]);
    }

    public function roomBans()
    {
        $user = Auth::user();
        return $this->hasOne('App\Models\RoomBans', 'chat_room_id', 'id')->where(['user_id' => $user->id]);
    }

    public function chatAttachment()
    {
        return $this->belongsTo('App\Models\ChatAttachment', 'id', 'chat_room_id');
    }

    public function roomUsers()
    {
        return $this->hasOne('App\Models\RoomUsers', 'chat_room_id', 'id');
    }

    public function chatRoom()
    {
        return $this->hasMany('App\Models\ChatRoom', 'chat_room_id', 'id');
    }


}
