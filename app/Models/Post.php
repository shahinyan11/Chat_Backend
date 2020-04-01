<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model  
{

    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    public $fillable = ['threadid', 'parentid', 'username', 'userid', 'title', 'dateline', 'pagetext', 'allowsmilie', 'showsignature', 'ipaddress', 'iconid', 'visible', 'attach', 'infraction', 'reportthreadid', 'post_thanks_amount'];

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

    public function user()
    {
        return $this->belongsTo('App\User', 'userid', 'id');
    }

}
