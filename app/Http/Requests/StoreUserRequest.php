<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Mude para 'true' para permitir que qualquer um tente criar
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string|max:14|unique:users,cpf',
            'profile_id' => 'required|exists:profiles,id',

            'addresses' => 'sometimes|array',
            'addresses.*.street' => 'required_with:addresses|string|max:255',
            'addresses.*.zip_code' => 'required_with:addresses|string|max:20',
        ];
    }
}