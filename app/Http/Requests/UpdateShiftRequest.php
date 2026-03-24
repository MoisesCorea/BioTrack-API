<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:shifts,name,' . $this->route('shift'),
            'entry_time' => 'required|date_format:H:i:s',
            'finish_time' => 'required|date_format:H:i:s',
            'shift_duration' => 'required|integer',
            'monthly_late_allowance' => 'required|integer',
            'days' => 'required'
        ];
    }
}
