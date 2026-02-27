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
        'numero_telephone'=>['sometimes','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
    ];
    }
    public function messages()
    {
        return [
            'numero_telephone.regex' => 'Le numéro doit commencer par un indicatif international, ex: +21612345678',
        ];
    }
}
