<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
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
        return [
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'state' => 'nullable|string',
            'address1' => 'required|string',
            'address2' => 'nullable|string',
            'postal_code' => 'nullable|string',
        ];
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
