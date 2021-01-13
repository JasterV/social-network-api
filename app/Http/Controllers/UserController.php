<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRequest;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\FollowRequest;
use App\Models\Follows;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function get_user(ApiRequest $request, string $username)
    {
        try {
            $user = User::where('username', $username)->firstOrFail();
            $user->num_followers = Follows::where("followed_id", $user->id)->count();
            $user->num_following = Follows::where("follower_id", $user->id)->count();
            $user->num_posts = Post::where("user_id", $user->id)->count();
            $user->following = Follows::is_following($request->user(), $username);
            return Controller::sendResponse(["user" => $user], 'success');
        } catch (\Throwable $_) {
            return Controller::sendError("Can't found the user", [], 404);
        }
    }

    public function delete_account(ApiRequest $request)
    {
        $user = $request->user();
        User::destroy($user->id);
        DB::table('oauth_access_tokens')
            ->where('user_id', Auth::user()->id)
            ->update([
                'revoked' => true
            ]);
        return Controller::sendResponse(["user" => $user], 'success');
    }

    public function edit_user(EditProfileRequest $request)
    {
        $data = $request->validated();
        User::where('id', $request->user()->id)->update($data);
        return Controller::sendResponse($data, 'success');
    }

    public function get_followers(string $username)
    {
        $users = User::get_followers($username)->select("u2.profile_image", "u2.username")->get();
        return Controller::sendResponse(["users" => $users], 'success');
    }

    public function get_following(string $username)
    {
        $users = User::get_following($username)->select("users.profile_image", "users.username")->get();
        return Controller::sendResponse(["users" => $users], 'success');
    }

    public function follow_user(FollowRequest $request)
    {
        $username = $request->username;
        $user = $request->user();
        $follow = User::follow_user($user, $username);
        return Controller::sendResponse($follow, 'success');
    }

    public function unfollow_user(FollowRequest $request)
    {
        $username = $request->username;
        $user = $request->user();
        User::unfollow_user($user, $username);
        return Controller::sendResponse([], 'success');
    }

    public function search_by_name(ApiRequest $request, string $name)
    {
        $follow = $request->query("follow");
        $user = $request->user();
        $paginator = [];
        if ($follow == 'true') {
            $paginator = User::get_following($user->username)
                ->where("users.username", "LIKE", "%$name%")
                ->select("users.id", "users.username", "users.profile_image")
                ->paginate(10);
        } else {
            $paginator = User::where("username", "LIKE", "%$name%")
                ->select("users.id", "users.username", "users.profile_image")
                ->paginate(10);
        }
        return Controller::sendResponse([
            "items" => $paginator->items(),
            "next_page_url" => $paginator->nextPageUrl()
        ], 'success');
    }
}
