<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatPostReply extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_post_reply';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['chat_post_id', 'owner_id', 'body', 'report_count', 'created_at', 'updated_at'];

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
        return $this->belongsTo('App\Models\ChatPost', 'chat_post_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'owner_id', 'id');
    }

}
