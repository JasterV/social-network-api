<?php

namespace App\Models;

use App\Http\Requests\ApiRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'image_url',
        'tags'
    ];

    public static function create_post(object $user, array $content)
    {
        $new_post = Post::create([
            "user_id" => $user->id,
            "description" => $content["description"],
            "image_url" => $content["image_url"]
        ]);
        $new_post->likes = 0;
        $new_post->comments = 0;
        $new_post->liked = false;

        Tags::tag_users($user->id, $new_post->id, $content["tags"]);

        $new_post->tags = Tags::get_post_tagged_users($new_post->id);

        return  [
            "post" => $new_post,
            "user" => [
                "username" => $user->username,
                "profile_image" => $user->profile_image
            ],
        ];
    }

    public static function get_post_by_id(ApiRequest $request, string $id)
    {
        $post = Post::findOrFail($id);
        $user = User::find($post->user_id);
        Post::set_post_additional_info($post, $request->user()->id);
        return [
            "post" => $post,
            "user" => [
                "username" => $user->username,
                "profile_image" => $user->profile_image
            ],
        ];
    }

    public static function get_tagged_posts(ApiRequest $request, string $username)
    {
        $user = User::where("username", $username)->firstOrFail();
        $paginator = Post::join("tags", function ($join) use ($user) {
            $join->on("posts.id", '=', 'tags.post_id')
                ->where("tags.user_id", '=', $user->id);
        })
            ->select("posts.*")
            ->latest("posts.created_at")
            ->paginate(15);
        return [
            "items" => Post::get_mapped_posts($request, $paginator->items()),
            "next_page_url" => $paginator->nextPageUrl()
        ];
    }

    public static function get_shared_posts(ApiRequest $request, string $username)
    {
        $user = User::where("username", $username)->firstOrFail();
        $paginator = Post::join("shares", function ($join) use ($user) {
            $join->on("posts.id", '=', 'shares.post_id')
                ->where("shares.user_id", '=', $user->id);
        })
            ->latest("posts.created_at")
            ->select("posts.*")
            ->paginate(15);
        return [
            "items" => Post::get_mapped_posts($request, $paginator->items()),
            "next_page_url" => $paginator->nextPageUrl()
        ];
    }

    public static function get_user_posts(ApiRequest $request, string $username)
    {
        $user = User::where("username", $username)->firstOrFail();
        $paginator = Post::where("user_id", $user->id)
            ->latest("created_at")
            ->paginate(15);
        return [
            "items" => Post::get_mapped_posts($request, $paginator->items(), $user),
            "next_page_url" => $paginator->nextPageUrl()
        ];
    }

    public static function get_home_posts(ApiRequest $request, string $username)
    {
        $user = User::where("username", $username)->firstOrFail();
        $paginator = Post::where("user_id", $user->id)
            ->union(
                User::join("follows", function ($join) use ($user) {
                    $join->on("users.id", "=", "follows.followed_id")
                        ->where("follows.follower_id", '=', $user->id);
                })
                    ->join("posts", "posts.user_id", "=", "users.id")
                    ->select("posts.*")
            )
            ->latest("created_at")
            ->paginate(15);
        return [
            "items" => Post::get_mapped_posts($request, $paginator->items()),
            "next_page_url" => $paginator->nextPageUrl()
        ];
    }

    public static function set_post_additional_info(&$post, string $user_id)
    {
        $post->likes = Likes::where("post_id", $post->id)->count();
        $post->comments = Comment::where("post_id", $post->id)->count();
        $post->liked = Likes::user_like_post($user_id, $post->id);
        $post->tags = Tags::get_post_tagged_users($post->id);
    }

    private static function get_mapped_posts(ApiRequest $request, array $posts, object $user = null)
    {
        return array_map(
            function ($post) use ($user, $request) {
                Post::set_post_additional_info($post, $request->user()->id);
                if ($user == null) {
                    $user = User::find($post->user_id);
                }
                return [
                    "post" => $post,
                    "user" => [
                        "username" => $user->username,
                        "profile_image" => $user->profile_image
                    ],
                ];
            },
            $posts
        );
    }
}
