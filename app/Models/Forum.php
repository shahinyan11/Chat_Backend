<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Forum extends Model  
{

    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'forum';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['id','title', 'title_clean', 'description', 'description_clean', 'options', 'order', 'password', 'private', 'replycount', 'daysprune', 'parentid', 'parentlist', 'childlist', 'threadcount', 'lastthread', 'lastthreadid', 'lastpost', 'lastposter', 'lastposterid', 'lastpostid'];

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
