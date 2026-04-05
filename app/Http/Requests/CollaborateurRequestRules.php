<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollaborateurRequestRules extends FormRequest
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
            'email' => 'required|email|unique:users,email',
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'phone_number' => ['required','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
            'date_of_hire' => 'required|date',
            'role' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'phone_number.regex' => 'Le numéro doit commencer par un indicatif international, ex: +21612345678',
        ];
    }
}
