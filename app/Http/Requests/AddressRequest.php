<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'address'    => ['required', 'string', 'max:500'],
            'city'       => ['required', 'string', 'max:100'],
            'state'      => ['nullable', 'string', 'max:100'],
            'country'    => ['required', 'string', 'max:100'],
            'zip'        => ['nullable', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ];
    }
}
