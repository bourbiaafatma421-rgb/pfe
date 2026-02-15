<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjoutManagerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        // Option 1 : autoriser uniquement si aucun manager n'existe
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
            'email'  => 'required|email|unique:users,email',
            'nom'    => 'required|string|max:25',
            'prenom' => 'required|string|max:25',
            'date_recrutement' => 'required|date',
            'numero_telephone' => ['required','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
        ];
    }

    public function messages()
    {
        return [
            'email.required'=> 'Email obligatoire.',
            'email.email'=> 'Email invalide.',
            'email.unique'=> 'Manager déjà existant.',
            'nom.required'=> 'Nom obligatoire.',
            'nom.max'=> 'Nom trop long.',
            'prenom.required'=> 'Prénom obligatoire.',
            'prenom.max'=> 'Prénom trop long.',
            'date_recrutement.*'=> 'Date invalide ou manquante.',
            'numero_telephone.*'=> 'Téléphone invalide ou manquant.',
        ];
    }
}
