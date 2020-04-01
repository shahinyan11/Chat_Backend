<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChatAttachment extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_attachment';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['owner_id', 'type', 'src_id', 'chat_post_id', 'chat_room_id', 'score', 'created_at', 'updated_at'];

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

    public function chatRoom()
    {
        return $this->hasOne('App\Models\ChatRoom', 'chat_room_id', 'id');
    }

    public function chatPost()
    {
        return $this->belongsTo('App\Models\ChatPost', 'chat_post_id', 'id');
    }

    public function userImage()
    {
        return $this->hasOne('App\Models\UserImage', 'id', 'src_id');
    }

    public function chatAttachmentVotes()
    {
        return $this->hasOne('App\Models\ChatAttachmentVotes', 'chat_attachment_id', 'id');
    }

    public function report()
    {
        $user = Auth::user();
        return $this->hasOne('App\Models\Reports', 'property_id', 'id')->where([
            'property_type' => 'chat_attachment',
            'reported_by' => $user->id
        ]);
    }
}
