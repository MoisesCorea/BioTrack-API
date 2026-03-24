<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:shifts,name',
            'entry_time' => 'required|date_format:H:i:s',
            'finish_time' => 'required|date_format:H:i:s',
            'shift_duration' => 'required|integer',
            'monthly_late_allowance' => 'required|integer',
            'days' => 'required'
        ];
    }
}
