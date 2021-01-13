<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follows extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'follower_id',
        'followed_id',
    ];

    public static function is_following(object $user, string $followed_username) {
        return Follows::join("users", function($join) use ($user, $followed_username) {
            $join->on("follows.followed_id", '=', "users.id")
            ->where("users.username", "=", $followed_username)
                    ->where("follows.follower_id", '=', $user->id);
        })->exists();
    }
}
