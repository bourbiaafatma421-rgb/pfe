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
        return [ 'email' => 'required|email|unique:users,email',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'numero_telephone' => ['required','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
            'poste' => 'required|string',
            'etat' => 'required|string|in:encours,terminer,Terminer,Encours',
        ];
    }

    public function messages()
    {
        return [
            'numero_telephone.regex' => 'Le numéro doit commencer par un indicatif international, ex: +21612345678',
        ];
    }
}
