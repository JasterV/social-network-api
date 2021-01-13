<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'name',
        'date_of_birth',
        'email',
        'gender',
        'password',
        'marital_status',
        'description',
        'profile_image',
        'portrait_image'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function follow_user($follower, $username) {
        $followed = User::where("username", $username)->first();
        $follow = Follows::create([
            "follower_id" => $follower->id,
            "followed_id" => $followed->id
        ]);
        // SEND NOTIFICATION
        FollowNofitication::create([
            "recipient_id" => $followed->id,
            "actor_id" => $follower->id
        ]);
        return $follow;
    }

    public static function unfollow_user($follower, $username) {
        $followed = User::where("username", $username)->first();
        Follows::where("follower_id", $follower->id)
            ->where("followed_id", $followed->id)
            ->delete();
    }

    public static function get_followers($username) {
        return User::join('follows', function ($join) use ($username) {
            $join->on('follows.followed_id', '=', 'users.id')
                ->where('users.username', '=', "$username");
        })
        ->join("users AS u2", "u2.id", "=", "follows.follower_id");
    }

    public static function get_following($username) {
        return User::join('follows', 'follows.followed_id', '=', 'users.id')
        ->join('users AS u2', function ($join) use ($username) {
            $join->on('follows.follower_id', '=', 'u2.id')
                ->where('u2.username', '=', "$username");
        });
    }
}
