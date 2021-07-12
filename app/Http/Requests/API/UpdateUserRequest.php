<?php

namespace App\Http\Requests\API;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserRequest extends FormRequest
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
        return in_array($this->method(), ['POST', 'PATCH', 'PUT']) ?
            [
                'name' => 'max:100',
                'email' => 'email|unique:users,email,' . Auth::id(),
                'password' => 'confirmed|min:6',
            ] : [];
    }
}
