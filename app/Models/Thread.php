<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model  
{

    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'thread';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    public $fillable = ['firstpostid', 'lastpostid', 'lastpost', 'forum_id', 'pollid', 'open', 'replycount', 'postercount', 'hiddencount', 'deletedcount', 'postusername', 'postuserid', 'lastposter', 'lastposterid', 'dateline', 'views', 'iconid', 'visible', 'sticky', 'votenum', 'votetotal', 'attach', 'similar', 'taglist', 'keywords'];

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

}
