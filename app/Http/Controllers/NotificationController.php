<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRequest;
use App\Http\Requests\NotificationRequest;
use App\Models\FollowNofitication;
use App\Models\PostNofitication;

class NotificationController extends Controller
{
    public function get_notifications(ApiRequest $request)
    {
        $user = $request->user();

        $post_not = $this->get_posts_notifications($user->id);
        $follow_not = $this->get_follow_notifications($user->id);

        $result = $post_not->map(function ($elem) {
            return [
                "username" => $elem->username,
                "profile_image" => $elem->profile_image,
                "post_id" => $elem->post_id,
                "description" => $this->build_post_not_description($elem),
                "created_at" => $elem->created_at,
                "type" => "post"
            ];
        })->concat(
            $follow_not->map(function ($elem) {
                return [
                    "username" => $elem->username,
                    "profile_image" => $elem->profile_image,
                    "description" => "@{$elem->username}" . " started following you",
                    "created_at" => $elem->created_at,
                    "type" => "follow"
                ];
            })
        )->sortBy('created_at');

        return Controller::sendResponse([
            "notifications" => $result
        ], 'success');
    }

    public function mark_not_as_seen(NotificationRequest $request, string $id)
    {
        $valid =  $request->validated();
        $type = $valid["type"];
        try {
            if ($type == "post") {
                $noti = PostNofitication::findOrFail($id);
            } else {
                $noti = FollowNofitication::findOrFail($id);
            }
            $noti->seen = true;
            $noti->save();
            return Controller::sendResponse([], "success");
        } catch (\Throwable $_) {
            return Controller::sendError("Error opening the notification", [], 404);
        }
    }


    private function get_follow_notifications($user_id)
    {
        return FollowNofitication::join('users', function ($join) use ($user_id) {
            $join->on("users.id", "=", "follow_nofitications.actor_id")
                ->where("follow_nofitications.recipient_id", '=', $user_id)
                ->where("follow_nofitications.seen", '=', false);
        })
            ->latest("created_at")
            ->select("users.username", "users.profile_image", "follow_nofitications.created_at")
            ->get();
    }

    private function get_posts_notifications($user_id)
    {
        return PostNofitication::join('users', function ($join) use ($user_id) {
            $join->on("users.id", "=", "post_nofitications.actor_id")
                ->where("post_nofitications.recipient_id", '=', $user_id)
                ->where("post_nofitications.seen", '=', false);
        })
            ->latest("created_at")
            ->select("users.username", "users.profile_image", "post_nofitications.post_id", "post_nofitications.action", "post_nofitications.created_at")
            ->get();
    }

    private function build_post_not_description($row)
    {
        $action = $row->action;
        if ($action == "like") {
            return "@{$row->username} liked your post";
        } else if ($action == "comment") {
            return "@{$row->username} commented your post";
        } else if ($action == "share") {
            return "@{$row->username} shared your post";
        } else if ($action == "tag") {
            return "@{$row->username} tagged you on a post";
        }
    }
}
