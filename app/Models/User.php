<?php

namespace App\Models;

use App\Facades\Auth;
//use App\Models\OneSignal\Subscriber;
//use App\Models\Traits\UserFriendable;
//use Carbon\Carbon;
//use System\Core\Encryption;
//use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $connection = 'mysql2';
    protected $table = 'accounts_Users';
    protected $fillable = [
        'site_id',
        'screen_name',
        'path',
        'email',
        'password',
        'salt',
        'display_name_type',
        'gender',
        'about',
        'birthday',
        'first_name',
        'last_name',
        'avatar',
        'country_id',
        'region_id',
        'city_id',
        'note',
        'last_login',
        'last_ip_address',
        'login_count',
        'archive',
        'status',
        'votes_status'
    ];
    protected $observables = [
        'attached.relation',
        'detached.relation',
        'attached.favoritesPosts',
        'detached.favoritesPosts',
        'attached.favoritesAlbums',
        'detached.favoritesAlbums',
    ];
    protected $appends = [
        'display_name',
        'avatar_path',
        'user_path'
    ];

    protected $hidden = [
        'password',
    ];

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'modify_date';

    public function can($ability, $arguments = [])
    {
        return app(Gate::class)->forUser($this)->check($ability, $arguments);
    }


    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function dialogs()
    {
        return $this->hasMany(Dialog::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function latestAlbums()
    {
        return $this->albums()->with('tags')->active()->published()->limitMoreContribs();
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function forumAccounts()
    {
        return $this->hasMany(ForumAccounts::class);
    }

    public function activity()
    {
        return $this->hasMany(Activity::class, 'wall_id');
    }

    public function bans() {
        return $this->hasOne(Bans::class);
    }

    public function updates()
    {
        return $this->hasMany(Update::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function freeWeek()
    {
        return $this->hasOne(FreeWeek::class);
    }

    public function favorites($type)
    {
        $method = 'favorites' . ucfirst(str_plural($type));

        return $this->$method()->has('user');
    }

    public function oneSignalSubscriber()
    {
        return $this->hasOne(Subscriber::class);
    }

    public function favoritesAlbums()
    {
        return $this->morphedByMany(Album::class, 'favoriteable', 'favorites')->withTimestamps('created_at', 'updated_at');
    }

    public function favoritesPosts()
    {
        return $this->morphedByMany(Activity::class, 'favoriteable', 'favorites')->withTimestamps('created_at', 'updated_at');
    }

    public function getDisplayNameAttribute()
    {
        return $this->display_name_type == 'screen_name' ? $this->screen_name : $this->first_name . ' ' . $this->last_name;
    }

    public function getUserPathAttribute()
    {
        $path = $this->path != '' ? $this->id . '-' . $this->path : $this->id;
        return '/user/' . $path;
    }

    public function getOnlineStatusAttribute()
    {
        $status = $this->isOnline() ? 'online' : 'offline';

        return ucfirst($status);
    }

    public function getAgeAttribute()
    {
        return $this->birthday != "0000-00-00" ? Carbon::now()->diffInYears(Carbon::parse($this->birthday)) : 0;
    }

    public function getBirthdayDayAttribute()
    {
        return $this->birthday != "0000-00-00" ? Carbon::parse($this->birthday)->format('d'): '00';
    }

    public function getBirthdayMonthAttribute()
    {
        return $this->birthday != "0000-00-00" ? Carbon::parse($this->birthday)->format('m'): '00';
    }

    public function getBirthdayYearAttribute()
    {
        return $this->birthday != "0000-00-00" ? Carbon::parse($this->birthday)->format('Y'): '0000';
    }

    public function getAuthKeyAttribute()
    {
        $secretKey = "Aj190mfqjw9fajf";

        return Encryption::encode($this->email . ":" . $this->password, $secretKey);
    }

    public function getAvatarPathAttribute()
    {
        $images = [];

        foreach (['t', 'm'] as $size) {
            $images[$size] = $this->getAvatar($size);
        }

        return (object)$images;
    }

//    public function getAboutAttribute()
//    {
//        return \Emoji::replace($this->attributes['about']);
//    }

    public function isMember($siteId)
    {
        if( !Auth::logged() ) {
            return false;
        }

        if( !is_null($this->count_memberships) ) {
            return $this->count_memberships > 0;
        }

        $this->count_memberships = $this->memberships()->where('site_id', $siteId)->active()->count();


        return $this->count_memberships > 0;
    }

    public function getAvatar($size = 't')
    {
        if ($this->avatar == 'default') {
            $dimension = $size == 't' ? '150x150' : '50x50';
            $avatar = 'https://hwcdn.voyeurweb.com/uploads/users/default' . $dimension . $this->gender . '.jpg';
        } else {
            $avatar = 'https://hwcdn.voyeurweb.com/uploads/users/' . $this->id . '/' . $size . '_' . $this->avatar;
        }

        return $avatar;
    }

    public function getEncryptAuthAttribute()
    {
        return app('encrypter')->encrypt($this->id);
    }

    public function OneSignalSubscribed()
    {
        return $this->oneSignalSubscriber()
            ->where('site_id', SITE_ID)
            ->count();
    }
}
