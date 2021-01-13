<?php

namespace App\Http\Requests;

class EditPostRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'string',
            "tags"    => "array",
            "tags.*"  => "string|distinct|exists:users,username",
        ];
    }
}
