<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{

    public function testUnauthorized() {
        $this->get('/auth/logged')
            ->assertStatus(401)
            ->assertJson([
                "success" => false,
                "message" => "Unauthorized"
            ]);
    }

    public function testAuthorized() {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $this->get('/auth/logged')
            ->assertStatus(200)
            ->assertJson([
                "success" => true,
                "message" => "Authorized"
            ]);
    }

    public function testRegisterSuccessful()
    {
        $this->postJson('/auth/register', [
            'name' => 'Victor',
            'email' => 'jasterv@mail.com',
            'password' => 'patata',
            'c_password' => 'patata',
            'gender' => 'man',
            'marital_status' => 'single',
            'date_of_birth' => '2001-01-12',
            'username' => 'JasterV'
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user'
                ],
                'message'
            ])
            ->assertJson([
                "success" => true,
                "message" => "User register successfully."
            ]);
    }

    public function testLoginEmailSuccessful()
    {
        $user = User::factory()->create();
        $this->postJson('/auth/login', [
            'name_email' => $user->email,
            'password' => 'password',
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'token'
                ],
                'message'
            ])
            ->assertJson([
                "success" => true,
                "message" => "User login successfully."
            ]);
    }

    public function testLoginNameSuccessful()
    {
        $user = User::factory()->create();
        $this->postJson('/auth/login', [
            'name_email' => $user->username,
            'password' => 'password',
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'token'
                ],
                'message'
            ])
            ->assertJson([
                "success" => true,
                "message" => "User login successfully."
            ]);
    }

    public function testRegisterNotSuccessful()
    {
        $this->postJson('/auth/register', [
            'name' => 'Victor',
            'email' => 'sdgdfgdf',
            'password' => 'patata',
            'c_password' => 'patataaaa',
            'gender' => 'gfdhfgh',
            'marital_status' => 'dfgdfsgh',
            'date_of_birth' => '2001-30-12',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'username',
                    'email',
                    'c_password',
                    'gender',
                    'marital_status',
                    'date_of_birth'
                ],
                'message'
            ])
            ->assertJson([
                "success" => false,
                "message" => "Wrong credentials",
                "data" => [
                    "username" => [
                        "The username field is required."
                    ],
                    "email" => [
                        "The email must be a valid email address."
                    ],
                    "c_password" => [
                        "The c password and password must match."
                    ],
                    "gender" => [
                        "The selected gender is invalid."
                    ],
                    "marital_status" => [
                        "The selected marital status is invalid."
                    ],
                    "date_of_birth" => [
                        "The date of birth is not a valid date."
                    ],
                ]
            ]);
    }

    public function testLoginNotSuccessful()
    {

        $this->postJson('/auth/login', [
            'name_email' => "non.existent@mail.com",
            'password' => 'password',
        ])
            ->assertStatus(401)
            ->assertJson([
                "success" => false,
                "message" => "Unauthorized"
            ]);
    }
}
