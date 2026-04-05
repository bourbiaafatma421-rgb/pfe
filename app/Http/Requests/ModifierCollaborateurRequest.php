<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModifierCollaborateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['sometimes', 'string', 'exists:roles,name'],
            'phone_number' => ['sometimes', 'string', 'regex:/^\+?[0-9\s\-]{8,15}$/'],
        ];
    }

    public function messages()
    {
        return [
            'phone_number.regex' => 'Numéro invalide (ex: +21612345678)',
            'role.exists' => 'Le rôle sélectionné est invalide',
        ];
    }
}