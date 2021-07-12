<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordConfirmRequest extends FormRequest
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
        return $this->method() === 'POST' ?
            [
                'email' => 'required|exists:users',
                'password' => 'required|min:6|confirmed',
                'code' => 'required'
            ] : [];
    }
}
