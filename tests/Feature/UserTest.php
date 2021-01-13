<?php

namespace Tests\Feature;

use App\Models\Follows;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{

    public function testGetOwnUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->get("/users/{$user->username}")
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "user" => [
                        "id" => $user->id,
                        "username" => $user->username,
                        "name" => $user->name,
                        "email" => $user->email,
                        "num_followers" => 0,
                        "num_following" => 0,
                        "num_posts" => 0
                    ],
                ],
                "message" => "success"
            ]);
    }

    public function testGetOtherUser()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->get("/users/{$user2->username}")
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "user" => [
                        "id" => $user2->id,
                        "username" => $user2->username,
                        "name" => $user2->name,
                        "email" => $user2->email,
                        "num_followers" => 0,
                        "num_following" => 0,
                        "num_posts" => 0
                    ],
                ],
                "message" => "success"
            ]);
    }

    public function testGetNonExistentUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->get("/users/gfdghdfh", ["Accept", "application/json"])
            ->assertStatus(404)
            ->assertJson([
                "success" => false,
                "message" => "Can't found the user"
            ]);
    }

    public function testGetFollowingUser()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Follows::create([
            "follower_id" => $user->id,
            "followed_id" => $user2->id
        ]);

        $this->actingAs($user, 'api');

        $this->get("/users/{$user2->username}")
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "user" => [
                        "following" => true
                    ]
                ],
                "message" => "success"
            ]);
    }

    public function testEditProfile()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            "username" => "jasterv",
            "name" => "Victor",
            "marital_status" => "married",
            "description" => "Hi I'm Victor!",
            "name_visible" => false,
        ];

        $this->putJson("/users/edit", $data)
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => $data,
                "message" => "success"
            ]);
    }

    public function testEditUsernameToAlreadyExistent()
    {
        $user = User::factory()->create();
        User::factory()->create([
            "username" => "jasterv"
        ]);
        $this->actingAs($user, 'api');

        $data = [
            "username" => "jasterv",
            "name" => "Victor",
            "gender" => "woman",
            "marital_status" => "married",
            "description" => "Hi I'm Victor!",
            "name_visible" => false,
            "date_of_birth" => $user->date_of_birth,
        ];

        $this->putJson("/users/edit", $data)
            ->assertStatus(422)
            ->assertJson([
                "success" => false,
                "data" => [
                    "username" => ["Username already taken"]
                ],
                "message" => "Wrong credentials"
            ]);
    }

    public function testFollowUser()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->post("/users/follow", [
            "username" => $user2->username
        ])
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "follower_id" => 1,
                    "followed_id" => 2
                ],
                "message" => "success"
            ]);
    }

    public function testFollowYourself()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->post("/users/follow", [
            "username" => $user->username
        ])
            ->assertStatus(422)
            ->assertJson([
                "success" => false,
                "data" => [
                    "username" => ["Invalid username"]
                ],
                "message" => "Wrong credentials"
            ]);
    }

    public function testFollowNonExistentUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->post("/users/follow", [
            "username" => "asfddsgfsd"
        ])
            ->assertStatus(422)
            ->assertJson([
                "success" => false,
                "data" => [
                    "username" => ["Invalid username"]
                ],
                "message" => "Wrong credentials"
            ]);
    }

    public function testUnfollowUser()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Follows::create([
            "follower_id" => $user->id,
            "followed_id" => $user2->id
        ]);

        $this->actingAs($user, 'api');

        $this->post("/users/unfollow", [
            "username" => $user2->username
        ])
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "message" => "success"
            ]);
    }

    public function testSearchUser()
    {
        $names = [
            ["username" => "julia"],
            ["username" => "judit"],
            ["username" => "ajulo"],
            ["username" => "aaju23"],
            ["username" => "osopandaju23"],
            ["username" => "alaju45"],
            ["username" => "aajupiter"],
            ["username" => "aranjuez<3"],
            ["username" => "papapapajU"],
            ["username" => "ratataJuta"],
            ["username" => "papaJuJhones"],
            ["username" => "ericJuLopez23"],
            ["username" => "clojureForTheBrave"]
        ];

        foreach ($names as $_ => $value) {
            User::factory()->create($value);
        }

        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->get("/users/ju/search")
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "items" => [
                        ["username" => "julia"],
                        ["username" => "judit"],
                        ["username" => "ajulo"],
                        ["username" => "aaju23"],
                        ["username" => "osopandaju23"],
                        ["username" => "alaju45"],
                        ["username" => "aajupiter"],
                        ["username" => "aranjuez<3"],
                        ["username" => "papapapajU"],
                        ["username" => "ratataJuta"],
                    ]
                ],
                "message" => "success"
            ]);

        $this->get($response["data"]["next_page_url"])
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "items" => [
                        ["username" => "papaJuJhones"],
                        ["username" => "ericJuLopez23"],
                        ["username" => "clojureForTheBrave"]
                    ]
                ],
                "message" => "success"
            ]);
    }

    public function testSearchFollowingUser()
    {
        $user = User::factory()->create();
        $names = [
            ["username" => "julia"],
            ["username" => "judit"],
            ["username" => "ajulo"],
            ["username" => "aaju23"],
            ["username" => "osopandaju23"],
            ["username" => "alaju45"],
            ["username" => "aajupiter"],
            ["username" => "aranjuez<3"],
            ["username" => "papapapajU"],
            ["username" => "ratataJuta"],
            ["username" => "papaJuJhones"],
            ["username" => "ericJuLopez23"],
            ["username" => "clojureForTheBrave"]
        ];
        foreach ($names as $_ => $value) {
            User::factory()->create($value);
        }
        for ($i = 0; $i < 5; $i++) {
            User::follow_user($user, $names[$i]["username"]);
        }

        $this->actingAs($user, 'api');

        $this->get("/users/ju/search?follow=true")
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "items" => [
                        ["username" => "julia"],
                        ["username" => "judit"],
                        ["username" => "ajulo"],
                        ["username" => "aaju23"],
                        ["username" => "osopandaju23"],
                    ]
                ],
                "message" => "success"
            ]);
    }

    public function testGetFollowing()
    {
        $user = User::factory()->create();
        $names = [
            ["username" => "julia"],
            ["username" => "judit"],
            ["username" => "ajulo"],
        ];
        foreach ($names as $_ => $value) {
            User::factory()->create($value);
        }
        for ($i = 0; $i < count($names); $i++) {
            User::follow_user($user, $names[$i]["username"]);
        }

        $this->actingAs($user, 'api');

        $this->get("/users/{$user->username}/following")
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "users" => [
                        ["username" => "julia"],
                        ["username" => "judit"],
                        ["username" => "ajulo"],
                    ]
                ],
                "message" => "success"
            ]);
    }

    public function testGetFollowers()
    {
        $user = User::factory()->create();
        $julia = User::factory()->create(["username" => "julia"]);
        $judit = User::factory()->create(["username" => "judit"]);
        $ajulo = User::factory()->create(["username" => "ajulo"]);

        User::follow_user($julia, $user->username);
        User::follow_user($judit, $user->username);
        User::follow_user($ajulo, $user->username);

        $this->actingAs($user, 'api');

        $this->get("/users/{$user->username}/followers")
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "data" => [
                    "users" => [
                        ["username" => "julia"],
                        ["username" => "judit"],
                        ["username" => "ajulo"],
                    ]
                ],
                "message" => "success"
            ]);
    }
}
