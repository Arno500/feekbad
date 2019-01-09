<?php

namespace App;

use Auth;
use Debugbar;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use App\Events\FeedbacksUpdatedEvent;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'votes', 'author', 'done',
    ];

    protected $attributes = array(
        'votes' => 0,
        'done' => false,
    );

    protected $appends = array('votes', 'myVotes');

    protected $dispatchesEvents = [
        'saved' => FeedbacksUpdatedEvent::class
    ];

    //protected $hidden = array('votes');

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:U',
        'updated_at' => 'datetime:U',
    ];

    public function author()
    {
        return $this->belongsTo("App\User", 'author');
    }

    public function tags()
    {
        return $this->belongsToMany("App\Tag");
    }

    public function getUpdatedAtAttribute($value)
    {
        $date = new Carbon($value);
        $dateInSecondes = $date->format("U");
        return intval($dateInSecondes) * 1000;
    }

    public function votes()
    {
        return $this->belongsToMany('App\User', 'votes')->withPivot('number')->withTimestamps();
    }

    public function getVotesAttribute()
    {
        return intval($this->belongsToMany('App\User', 'votes')->withPivot('number')->sum("number"));
    }

    public function getMyVotesAttribute()
    {
        Debugbar::startMeasure("checkingAuth", "Checking Auth for My Votes");
        $authCheck = Auth::guard('api')->check();
        Debugbar::stopMeasure("checkingAuth");
        if ($authCheck) {
            $rows = $this->belongsToMany('App\User', 'votes')->withPivot('number')->where('user_id', "=", Auth::guard('api')->user()->getUserInfo()->id);
            if ($rows->count() == 0) {
                return null;
            } else {
                return intval($rows->sum("number"));
            }
        } else {
            return 0;
        }
    }

    /**
     * Get the comments for the feedback.
     */
    public function comments()
    {
        return $this->hasMany('App\Comment');
    }
}
