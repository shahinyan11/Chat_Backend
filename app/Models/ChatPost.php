<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChatPost extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_post';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['chat_room_id', 'owner_id', 'reply_to', 'type', 'body',  'attachment_id',  'report_count'];

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
    protected $casts = ['attachment' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public function chatRoom()
    {
        return $this->belongsTo('App\Models\ChatRoom', 'chat_room_id', 'id');
    }

    public function userInfo()
    {
        return $this->hasOne('App\Models\User', 'id', 'owner_id');
    }

    public function chatPostReply()
    {
        return $this->hasMany('App\Models\ChatPostReply', 'chat_post_id', 'id');
    }

    public function chatAttachment()
    {
        return $this->hasMany('App\Models\ChatAttachment', 'chat_post_id', 'id');
    }

    public function report()
    {
        $user = Auth::user();
        return $this->hasOne('App\Models\Reports', 'property_id', 'id')->where([
            'property_type' => 'chat_post',
            'reported_by' => $user->id
        ]);
    }

    public function chatPostVotes()
    {
        return $this->hasOne('App\Models\ChatPostVotes', 'chat_post_id', 'id');
    }

}
