<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_image';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['owner_id', 'property_type', 'property_id', 'privacy', 'width', 'height', 'src', 'created_at', 'updated_at'];

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
        return $this->belongsTo('App\Models\ChatAttachment', 'id', 'src_id');
    }

    public function messageAttachment()
    {
        return $this->belongsTo('App\Models\MessageAttachment', 'id', 'src_id');
    }

}
