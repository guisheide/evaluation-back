<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
        {
            return [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'cpf' => 'required|string|max:14|unique:users,cpf',
                'password' => 'required|string|min:8',

                'profile_id' => 'required|integer|exists:profiles,id',

                'addresses' => 'required|array|min:1',
                'addresses.*.street' => 'required|string',
                'addresses.*.number' => 'required|string',
                'addresses.*.neighborhood' => 'required|string',
                'addresses.*.city' => 'required|string',
                'addresses.*.state' => 'required|string|size:2',
                'addresses.*.zip_code' => 'required|string',
            ];
        }
}
