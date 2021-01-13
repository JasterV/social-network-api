<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    public function testFollowsNotify()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $this->postJson("/users/follow", [
            "username" => $user2->username
        ])
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->actingAs($user2, 'api');

        $this->get("/notifications/all")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "notifications" => [
                            [
                                "description" => "@{$user->username} started following you"
                            ]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testLikesNotify()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $data = Post::create_post($user2, $post_data);

        $this->post(
            "/posts/{$data["post"]->id}/like"
        )
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->actingAs($user2, 'api');

        $this->get("/notifications/all")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "notifications" => [
                            [
                                "description" => "@{$user->username} liked your post"
                            ]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testCommentNotify()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $data = Post::create_post($user2, $post_data);

        $this->postJson("/posts/{$data["post"]->id}/comment", [
            "comment" => "My first comment!"
        ])
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "comment" => [
                            "body" => "My first comment!"
                        ]
                    ],
                    "message" => "success"
                ]
            );

        $this->actingAs($user2, 'api');

        $this->get("/notifications/all")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "notifications" => [
                            [
                                "description" => "@{$user->username} commented your post"
                            ]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testShareNotify()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $data = Post::create_post($user2, $post_data);

        $this->post("/posts/{$data["post"]->id}/share")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->actingAs($user2, 'api');

        $this->get("/notifications/all")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "notifications" => [
                            [
                                "description" => "@{$user->username} shared your post"
                            ]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testTagNotify()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [$user2->username],
        ];

        Post::create_post($user, $post_data);

        $this->actingAs($user2, 'api');

        $this->get("/notifications/all")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "notifications" => [
                            [
                                "description" => "@{$user->username} tagged you on a post"
                            ]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testSetNotificationAsSeen()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [$user2->username],
        ];

        Post::create_post($user, $post_data);

        $this->actingAs($user2, 'api');

        $this->post("/notifications/1/seen", [
            "type" => "post"
        ])
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->get("/notifications/all")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [],
                    "message" => "success"
                ]
            );
    }
}
