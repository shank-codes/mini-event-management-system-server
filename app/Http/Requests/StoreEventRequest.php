<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required|date', // accept timezone-aware ISO string or naive (assume IST)
            'end_time' => 'required|date|after:start_time',
            'max_capacity' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Event name is required.',
            'start_time.required' => 'Start time is required.',
            'end_time.after' => 'End time must be after start time.',
            'max_capacity.min' => 'Max capacity must be at least 1.',
        ];
    }
}