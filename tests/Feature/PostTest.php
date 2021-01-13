<?php

namespace Tests\Feature;

use App\Models\Follows;
use App\Models\Likes;
use App\Models\Post;
use App\Models\Shares;
use App\Models\Tags;
use App\Models\User;
use Tests\TestCase;

class PostTest extends TestCase
{
    public function testPublishPost()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $this->postJson("/posts/publish", [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ])
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "post" => [
                        "description" => "Hi! This is my first post",
                        "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
                        "tags" => [],
                    ],
                    "user" => [
                        "username" => $user->username,
                        "profile_image" => $user->profile_image
                    ]
                ],
                "message" => "success"
            ]);
    }

    public function testPublishNonValidPost()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $this->postJson("/posts/publish", [
            "description" => null,
            "image_url" => "dfvgfhfghdsf",
            "tags" => null,
        ])
            ->assertStatus(422)
            ->assertJson([
                "success" => false,
                "data" => [
                    "description" => [
                        "The description must be a string."
                    ],
                    "image_url" => [
                        "The image url format is invalid."
                    ],
                    "tags" => [
                        "The tags must be an array."
                    ]
                ],
                "message" => "Wrong credentials"
            ]);
    }

    public function testGetPostById()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];
        $data = Post::create_post($user, $post_data);
        $post = $data["post"];

        $this->get("/posts/{$post->id}")
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "post" => $post_data,
                    "user" => [
                        "username" => $user->username,
                        "profile_image" => $user->profile_image
                    ]
                ],
                "message" => "success"
            ]);
    }

    public function testGetNonExistentPostById()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->get("/posts/23")
            ->assertStatus(404)
            ->assertJson([
                "success" => false,
                "message" => "Post not found"
            ]);
    }

    public function testPublishPostWithTags()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        User::factory()->create();
        $this->actingAs($user, 'api');

        $data = Post::create_post($user, [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [$user2->username, $user3->username],
        ]);

        $this->get("/posts/{$data["post"]->id}")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => [
                            "description" => "Hi! This is my first post",
                            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
                            "tags" => [
                                [
                                    "username" => $user2->username,
                                    "profile_image" => null
                                ],
                                [
                                    "username" => $user3->username,
                                    "profile_image" => null
                                ],
                            ],
                        ],
                        "user" => [
                            "username" => $user->username,
                            "profile_image" => $user->profile_image
                        ],
                    ],
                    "message" => "success"
                ]
            );
    }


    public function testGetUserPosts()
    {
        $user = User::factory()->create();
        User::factory()->create();

        $this->actingAs($user, 'api');

        $post1_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $post2_data = [
            "description" => "Hi! How are you",
            "image_url" => null,
            "tags" => []
        ];

        Post::create_post($user, $post1_data);
        sleep(2);
        Post::create_post($user, $post2_data);

        $this->get("/posts/{$user->username}/all")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "next_page_url" => null,
                        "items" => [
                            ["post" => ["id" => 2]],
                            ["post" => ["id" => 1]]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testGetUserSharedPosts()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post1_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $post2_data = [
            "description" => "Hi! How are you",
            "image_url" => null,
            "tags" => []
        ];

        $post1 = Post::create_post($user2, $post1_data);
        sleep(2);
        $post2 = Post::create_post($user2, $post2_data);

        Shares::create([
            "user_id" => $user->id,
            "post_id" => $post1["post"]->id,
        ]);

        Shares::create([
            "user_id" => $user->id,
            "post_id" => $post2["post"]->id,
        ]);

        $this->get("/posts/{$user->username}/shared")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "next_page_url" => null,
                        "items" => [
                            ["post" => ["id" => 2]],
                            ["post" => ["id" => 1]]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testGetUserTaggedPosts()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post1_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $post2_data = [
            "description" => "Hi! How are you",
            "image_url" => null,
            "tags" => []
        ];

        $post1 = Post::create_post($user2, $post1_data);
        sleep(2);
        $post2 = Post::create_post($user2, $post2_data);

        Tags::tag_users($user2->id, $post1["post"]->id, [$user->username]);
        Tags::tag_users($user2->id, $post2["post"]->id, [$user->username]);

        $this->get("/posts/{$user->username}/tagged")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "next_page_url" => null,
                        "items" => [
                            ["post" => ["id" => 2]],
                            ["post" => ["id" => 1]]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testGetHomePosts()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $post1 = Post::create_post($user2, $post_data);
        sleep(1);
        $post2 = Post::create_post($user2, $post_data);
        sleep(1);
        $post3 = Post::create_post($user3, $post_data);
        sleep(1);
        $post4 = Post::create_post($user3, $post_data);
        sleep(1);
        $post5 = Post::create_post($user, $post_data);
        sleep(1);
        $post6 = Post::create_post($user, $post_data);

        Follows::create([
            "follower_id" => $user->id,
            "followed_id" => $user2->id
        ]);

        $this->get("/posts/home")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "next_page_url" => null,
                        "items" => [
                            ["post" => ["id" => 6]],
                            ["post" => ["id" => 5]],
                            ["post" => ["id" => 2]],
                            ["post" => ["id" => 1]]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testSearchPostByDescription()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $post2_data = [
            "description" => "Hi! This is my second post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $post3_data = [
            "description" => "Hi! This is our third post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        Post::create_post($user2, $post_data);
        sleep(1);
        Post::create_post($user, $post2_data);
        Post::create_post($user, $post3_data);

        $this->get("/posts/my/search")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "next_page_url" => null,
                        "items" => [
                            ["id" => 2],
                            ["id" => 1],
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testLikePost()
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

        $this->post("/posts/{$data["post"]->id}/like")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->get("/posts/{$data["post"]->id}")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => ["liked" => true]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testLikeNonExistentPost()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $this->post("/posts/23/like")
            ->assertStatus(404)
            ->assertJson(
                [
                    "success" => false,
                    "message" => "Post not found"
                ]
            );
    }

    public function testUnlikePost()
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

        $this->post("/posts/{$data["post"]->id}/like")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->post("/posts/{$data["post"]->id}/unlike")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->get("/posts/{$data["post"]->id}")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => ["liked" => false]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testCommentPost()
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
    }

    public function testInvalidComment()
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

        $this->post("/posts/{$data["post"]->id}/comment")
            ->assertStatus(422)
            ->assertJson(
                [
                    "success" => false,
                    "message" => "Wrong credentials"
                ]
            );
    }

    public function testSharePost()
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

        $this->postJson("/posts/{$data["post"]->id}/share")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->get("/posts/{$user->username}/shared")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "next_page_url" => null,
                        "items" => [
                            [
                                "post" => [
                                    "id" => 1,
                                    "description" => $data["post"]->description
                                ]
                            ],
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testShareNonExistentPost()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $this->post("/posts/23/share")
            ->assertStatus(404)
            ->assertJson(
                [
                    "success" => false,
                    "message" => "Post not found"
                ]
            );
    }

    public function testGetPostLikers()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $data = Post::create_post($user, $post_data);
        $this->actingAs($user2, 'api');

        $this->post("/posts/{$data["post"]->id}/like")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->actingAs($user3, 'api');

        $this->post("/posts/{$data["post"]->id}/like")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->get("/posts/{$data["post"]->id}/likers")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "users" => [
                            ["username" => $user2->username],
                            ["username" => $user3->username]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testGetPostCommenters()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        User::factory()->create();

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $data = Post::create_post($user, $post_data);
        $this->actingAs($user2, 'api');

        $this->postJson("/posts/{$data["post"]->id}/comment", [
            "comment" => "Yay! great post!"
        ])
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->actingAs($user3, 'api');

        $this->postJson("/posts/{$data["post"]->id}/comment", [
            "comment" => "Yay! super great post!"
        ])
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );

        $this->get("/posts/{$data["post"]->id}/commenters")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "users" => [
                            ["username" => $user2->username],
                            ["username" => $user3->username]
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testDeletePost()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];


        $this->postJson("/posts/publish", $post_data)
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => $post_data
                    ],
                    "message" => "success"
                ]
            );

        $this->delete("/posts/1")
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "message" => "success"
                ]
            );
    }

    public function testDeleteUnauthorizedPost()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];


        $this->postJson("/posts/publish", $post_data)
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => $post_data
                    ],
                    "message" => "success"
                ]
            );

        $this->actingAs($user2, 'api');

        $this->delete("/posts/1")
            ->assertStatus(403)
            ->assertJson(
                [
                    "success" => false,
                    "message" => "You can't remove this post"
                ]
            );
    }

    public function testDeleteNonExistentPost()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $this->delete("/posts/128")
            ->assertStatus(404)
            ->assertJson(
                [
                    "success" => false,
                    "message" => "Post not found"
                ]
            );
    }

    public function testEditPost()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];


        $this->postJson("/posts/publish", $post_data)
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => $post_data
                    ],
                    "message" => "success"
                ]
            );

        $this->putJson("/posts/1/edit", [
            "description" => "This is my new description",
            "tags" => []
        ])
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => [
                            "description" => "This is my new description",
                        ]
                    ],
                    "message" => "success"
                ]
            );
    }

    public function testEditNonExistentPost()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->putJson("/posts/1/edit", [
            "description" => "This is my new description",
            "tags" => []
        ])
            ->assertStatus(404)
            ->assertJson(
                [
                    "success" => false,
                    "message" => "Post not found"
                ]
            );
    }

    public function testInvalidEdit()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];


        $this->postJson("/posts/publish", $post_data)
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => $post_data
                    ],
                    "message" => "success"
                ]
            );

        $this->putJson("/posts/1/edit", [
            "description" => null,
        ])
            ->assertStatus(422)
            ->assertJson(
                [
                    "success" => false,
                    "data" => [
                        "description" => ["The description must be a string."],
                    ],
                    "message" => "Wrong credentials"
                ]
            );
    }

    public function testEditUnauthorizedPost()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user, 'api');

        $post_data = [
            "description" => "Hi! This is my first post",
            "image_url" => "https://wallpapercave.com/wp/wp2279646.png",
            "tags" => [],
        ];

        $this->postJson("/posts/publish", $post_data)
            ->assertStatus(200)
            ->assertJson(
                [
                    "success" => true,
                    "data" => [
                        "post" => $post_data
                    ],
                    "message" => "success"
                ]
            );

        $this->actingAs($user2, 'api');

        $this->putJson("/posts/1/edit", [
            "description" => "This is my new description",
            "tags" => []
        ])
            ->assertStatus(403)
            ->assertJson(
                [
                    "success" => false,
                    "message" => "You can't edit this post"
                ]
            );
    }
}
