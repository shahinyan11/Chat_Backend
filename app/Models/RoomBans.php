<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomBans extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'room_bans';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'chat_room_id',];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function chatRoom()
    {
        return $this->belongsTo('App\Models\ChatRoom', 'chat_room_id', 'id');
    }

}
