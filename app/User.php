<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;


use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    protected $connection = 'mysql2';

    protected $table = 'accounts_Users';

    use HasApiTokens, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin(){
        return 1;
        return $this->role == 'admin';
    }

    public function chatPostReply()
    {
        return $this->hasMany('App\Models\ChatPostReply', 'owner_id', 'id');
    }
}
