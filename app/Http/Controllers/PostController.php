<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRequest;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\EditPostRequest;
use App\Http\Requests\PostRequest;
use App\Models\Comment;
use App\Models\Likes;
use App\Models\Post;
use App\Models\Shares;
use App\Models\Tags;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function new_post(PostRequest $request)
    {
        $user = $request->user();
        $body = $request->validated();
        $data = Post::create_post($user, $body);
        return Controller::sendResponse($data, "success");
    }

    public function get_post(ApiRequest $request, string $id)
    {
        try {
            $data = Post::get_post_by_id($request, $id);
            return Controller::sendResponse($data, "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Post not found", [], 404);
        }
    }

    public function get_likers(string $id)
    {
        $data = User::join("likes", function ($join) use ($id) {
            $join->on("users.id", "=", "likes.user_id")
                ->where("likes.post_id", '=', $id);
        })
            ->select("users.username", "users.profile_image")
            ->get();
        return Controller::sendResponse(["users" => $data], "success");
    }

    public function get_commenters(string $id)
    {
        $data = User::join("comments", function ($join) use ($id) {
            $join->on("users.id", "=", "comments.user_id")
                ->where("comments.post_id", '=', $id);
        })
            ->select("users.username", "users.profile_image")
            ->get();
        return Controller::sendResponse(["users" => $data], "success");
    }

    public function get_tags(string $id)
    {
        $tags = User::join("tags", function ($join) use ($id) {
            $join->on("users.id", "=", "tags.user_id")
                ->where("tags.post_id", '=', $id);
        })
            ->select("users.username", "users.profile_image")
            ->get();
        return Controller::sendResponse([
            "tags" => $tags
        ], "success");
    }

    public function get_comments(string $id)
    {
        $paginator = Comment::join("users", function ($join) use ($id) {
            $join->on("comments.user_id", '=', "users.id")
                ->where("comments.post_id", '=', $id);
        })
            ->select("comments.*", "users.username", "users.profile_image")
            ->latest("created_at")
            ->paginate(30);
        return Controller::sendResponse([
            "items" => $paginator->items(),
            "next_page_url" => $paginator->nextPageUrl()
        ], "success");
    }

    public function delete_post(ApiRequest $request, string $id)
    {
        try {
            $post = Post::findOrFail($id);
            if ($post->user_id != $request->user()->id) {
                return Controller::sendError("You can't remove this post", [], 403);
            }
            return Controller::sendResponse(["post" => $post], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Post not found", [], 404);
        }
    }

    public function like(ApiRequest $request, string $id)
    {
        try {
            $post = Post::findOrFail($id);
            $like = Likes::create([
                "user_id" => $request->user()->id,
                "post_id" => $post->id,
            ]);
            Likes::notify($request->user()->id, $post->user_id, $post->id);
            return Controller::sendResponse(["like" => $like], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Post not found", [], 404);
        }
    }

    public function unlike(ApiRequest $request, string $id)
    {
        try {
            $post = Post::findOrFail($id);
            Likes::where("user_id", $request->user()->id)
                ->where("post_id", $post->id)
                ->delete();
            return Controller::sendResponse([], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Post not found", [], 404);
        }
    }

    public function comment(CommentRequest $request, string $id)
    {
        $body = $request->validated();
        try {
            $post = Post::findOrFail($id);
            $comment = Comment::create([
                "user_id" => $request->user()->id,
                "post_id" => $post->id,
                "body" => $body["comment"]
            ]);
            Comment::notify($request->user()->id, $post->user_id, $post->id);
            return Controller::sendResponse(["comment" => $comment], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Post not found", [], 404);
        }
    }

    public function share(ApiRequest $request, string $id)
    {
        try {
            $post = Post::findOrFail($id);
            $share = Shares::create([
                "user_id" => $request->user()->id,
                "post_id" => $post->id,
            ]);
            Shares::notify($request->user()->id, $post->user_id, $post->id);
            return Controller::sendResponse(["share" => $share], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Post not found", [], 404);
        }
    }

    public function edit_post(EditPostRequest $request, string $id)
    {
        $body = $request->validated();
        try {
            $post = Post::findOrFail($id);
            if ($post->user_id != $request->user()->id) {
                return Controller::sendError("You can't edit this post", [], 403);
            }
            $post->description = $body["description"];
            $post->save();
            Tags::where("post_id", $post->id)->delete();
            Tags::tag_users($request->user()->id, $post->id, $body["tags"]);
            return Controller::sendResponse(["post" => $post], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Post not found", [], 404);
        }
    }

    public function user_posts(ApiRequest $request, string $username)
    {
        try {
            $data = Post::get_user_posts($request, $username);
            return Controller::sendResponse($data, 'success');
        } catch (\Throwable $_) {
            return Controller::sendError("User not found", [], 404);
        }
    }

    public function user_shared_posts(ApiRequest $request, string $username)
    {
        try {
            $data = Post::get_shared_posts($request, $username);
            return Controller::sendResponse($data, 'success');
        } catch (\Throwable $_) {
            return Controller::sendError("User not found", [], 404);
        }
    }

    public function user_tagged_posts(ApiRequest $request, string $username)
    {
        try {
            $data = Post::get_tagged_posts($request, $username);
            return Controller::sendResponse($data, 'success');
        } catch (\Throwable $_) {
            return Controller::sendError("User not found", [], 404);
        }
    }

    public function home_posts(ApiRequest $request)
    {
        $user = $request->user();
        $data = Post::get_home_posts($request, $user->username);
        return Controller::sendResponse($data, 'success');
    }

    public function search_by_description(string $text)
    {
        $paginator = Post::where("description", "LIKE", "%$text%")
            ->select("posts.id", "posts.image_url", "posts.description")
            ->latest("created_at")
            ->paginate(10);
        return Controller::sendResponse([
            "items" => $paginator->items(),
            "next_page_url" => $paginator->nextPageUrl()
        ], 'success');
    }

    public function delete_comment(ApiRequest $request, string $id)
    {
        $user = $request->user();
        try {
            $comment = Comment::findOrFail($id);
            if ($user->id != $comment->user_id) {
                return Controller::sendError("Permission denied, you cannot delete this comment", [], 403);
            }
            $comment->delete();
            return Controller::sendResponse(["comment" => $comment], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Comment not found", [], 404);
        }
    }

    public function edit_comment(CommentRequest $request, string $id)
    {
        $user = $request->user();
        $body = $request->validated();
        try {
            $comment = Comment::findOrFail($id);
            if ($user->id != $comment->user_id) {
                return Controller::sendError("Permission denied, you cannot edit this comment", [], 403);
            }
            $comment->body = $body["comment"];
            $comment->save();
            return Controller::sendResponse(["comment" => $comment], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Comment not found", [], 404);
        }
    }
}
