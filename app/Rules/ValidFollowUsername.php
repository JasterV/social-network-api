<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ValidFollowUsername implements Rule
{

    private $curr_username;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $username)
    {
        $this->curr_username = $username;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $username = $value;
        return $username != $this->curr_username &&
                User::where('username', "$username")->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid username';
    }
}
