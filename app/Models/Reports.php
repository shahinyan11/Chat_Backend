<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reports extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['reported_by', 'property_type', 'property_id', 'action_taken', 'notes', 'action_by', 'action_date', 'created_at', 'updated_at'];

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
        return $this->belongsTo('App\Models\ChatPost', 'property_id', 'id');
    }

    public function chatAttachment()
    {
        return $this->belongsTo('App\Models\ChatAttachment', 'property_id', 'id');
    }

}
