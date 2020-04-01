<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParties extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'conversation_parties';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['conversation_id', 'parties', 'created_at', 'updated_at'];

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
        return $this->belongsTo('App\Models\conversation', 'coversation_id', 'id');
    }

}
