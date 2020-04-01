<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string$request
     */
    protected $table = 'conversation';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['message_count', 'created_at', 'updated_at'];

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

    public function conversationParties()
    {
        return $this->hasMany('App\Models\ConversationParties', 'conversation_id', 'id');
    }

    public function message()
    {
        return $this->belongsTo('App\Models\Message', 'id', 'conversation_id');
    }

}
