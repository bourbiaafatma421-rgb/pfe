<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjoutStaffRequest extends FormRequest
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
            'first_name' => 'required|string|max:25',
            'last_name' => 'required|string|max:25',
            'date_of_hire' => 'required|date',
            'phone_number' => ['required','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
        ];
    }
    public function messages(): array
    {
        return [
            'email.unique' => 'Un utilisateur avec cet email existe déjà.',
            'phone_number.regex'=>'le numero de telephone doit etre au format international '
        ];
    }
}
