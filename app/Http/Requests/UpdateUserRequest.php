<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'age' => 'required|integer',
            'gender' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->route('user'),
            'address' => 'required|string|max:255',
            'phone_number' => 'required|integer',
            'shift_id' => 'required|exists:shifts,id',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|string|in:Activo,Inactivo',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }
}
