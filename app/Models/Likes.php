<?php

namespace App\Models;

use App\Interfaces\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Likes extends Model implements Notifiable
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
    ];

    public static function user_like_post($user_id, $post_id): bool {
        return Likes::where("user_id", $user_id)->where("post_id", $post_id)->exists();
    }

    public static function notify($actor_id, $recipient_id, $post_id) {
        PostNofitication::create([
            "recipient_id" => $recipient_id,
            "actor_id" => $actor_id,
            "post_id" => $post_id,
            "action" => "like"
        ]);
    }
}
