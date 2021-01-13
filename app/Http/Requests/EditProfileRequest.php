<?php

namespace App\Http\Requests;

use App\Rules\ValidUsername;
use Illuminate\Foundation\Http\FormRequest;

class EditProfileRequest extends ApiRequest
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
            'name' => 'required|string',
            'username' => ['required', 
                            'regex:/^(?=[a-zA-Z0-9._]{3,20}$)(?!.*[_.]{2})[^_.].*[^_.]$/', 
                            new ValidUsername($this->user()->username)],
            'marital_status' => 'required|in:married,single,widow,divorced,complicated,relationship,swinger',
            'description' => 'required|string',
            'profile_image' => 'nullable|url',
            'portrait_image' => 'nullable|url',
            'name_visible' => 'required:boolean'
        ];
    }
}
