<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAttendeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required to register.',
            'email.required' => 'Email is required to register.',
            'email.email' => 'Please provide a valid email.',
        ];
    }
}