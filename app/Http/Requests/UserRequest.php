<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        switch($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
                return [
                    'name' => 'required|max:100',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|confirmed|min:6',
                ];
            case 'PUT':
            case 'PATCH':
                $user = $this->route()->user;
                return [
                    'name' => 'required|max:100',
                    'email' => 'email|unique:users,email,'.$user->id,
                    'password' => 'nullable|confirmed|min:6',
                ];
            default:break;
        }
        return [];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email is already taken',
            'email.email' => 'Email is not a valid email address',
        ];
    }
}
