<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'message';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['author_id', 'body', 'conversation_id', 'call_id', 'created_at', 'updated_at'];

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

    public function conversation()
    {
        return $this->hasOne('App\Models\Conversation', 'id', 'coversation_id');
    }

    public function messageAttachment()
    {
        return $this->hasMany('App\Models\MessageAttachment', 'message_id', 'id');
    }

    public function call()
    {
        return $this->hasOne('App\Models\Call', 'id', 'call_id');
    }

    public function userInfo()
    {
        return $this->hasOne('App\Models\User', 'id', 'author_id');
    }

}
