<?php

namespace App\Models;

use App\Interfaces\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model implements Notifiable
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
    ];

    public static function tag_users(string $publisher_id, string $post_id, array $usernames) {
        foreach($usernames as $username) {
            $user = User::where("username", $username)->first();
            Tags::create([
                "user_id" => $user->id,
                "post_id" => $post_id
            ]);
            Tags::notify($publisher_id, $user->id, $post_id);
        }
    }

    public static function get_post_tagged_users(string $id)
    {
        return Tags::join("users", function ($join) use ($id) {
            $join->on("users.id", '=', 'tags.user_id')
                ->where("tags.post_id", '=', $id);
        })
            ->select("users.username", "users.profile_image")
            ->get();
    }

    public static function notify($actor_id, $recipient_id, $post_id) {
        PostNofitication::create([
            "recipient_id" => $recipient_id,
            "actor_id" => $actor_id,
            "post_id" => $post_id,
            "action" => "tag"
        ]);
    }
}
