<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'picture', 'admin', 'firstvote', 'votes',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /* public function permissions()
    {
    return $this->belongsToMany('App\Permission');
    } */

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }
    public function feedbacks()
    {
        return $this->hasMany('App\Feedback', 'author');
    }

    public function votesTo()
    {
        return $this->belongsToMany('App\Feedback', 'votes');
    }

    public function votes()
    {
        return intval($this->belongsToMany('App\Feedback', 'votes')->withPivot('number')->where('done', '=', '0')->sum('number'));
    }
}
