<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModifierCollaborateurRequest extends FormRequest
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
        'poste'=>'sometimes|string',
        'phone_number'=>['sometimes','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
    ];
    }
    public function messages()
    {
        return [
            'phone_number.regex' => 'Le numéro doit commencer par un indicatif international, ex: +21612345678',
        ];
    }
}
