<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MassAttendanceReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department_id' => 'required|integer',
            'initial_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:initial_date',
            'event_id' => 'required|exists:events,id'
        ];
    }
}
