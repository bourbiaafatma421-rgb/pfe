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
            'first_name'    => 'required|string|max:25',
            'last_name' => 'required|string|max:25',
            'date_of_hire' => 'required|date',
            'phone_number' => ['required','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
        ];
    }

    public function messages()
    {
        return [
            'email.required'=> 'Email obligatoire.',
            'email.email'=> 'Email invalide.',
            'email.unique'=> 'Manager déjà existant.',
            'first_name.required'=> 'Nom obligatoire.',
            'first_name.max'=> 'Nom trop long.',
            'last_name.required'=> 'Prénom obligatoire.',
            'last_name.max'=> 'Prénom trop long.',
            'date_of_hire.*'=> 'Date invalide ou manquante.',
            'phone_number.*'=> 'Téléphone invalide ou manquant.',
        ];
    }
}
