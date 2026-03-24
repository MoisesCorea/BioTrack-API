<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:admins,email,' . $this->route('admin'),
            'alias' => 'required|string|max:50|unique:admins,alias,' . $this->route('admin'),
            'password' => 'nullable|string|min:8',
        ];
    }
}
