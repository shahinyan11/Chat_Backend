<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAttachmentVotes extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chat_attachment_votes';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'chat_attachment_id', 'like'];

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

    public function chatAttachment()
    {
        return $this->belongsTo('App\Models\ChatAttachment', 'chat_attachment_id', 'id');
    }

}
