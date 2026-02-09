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
            'nom'    => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Un manager existe déjà dans le système.'
        ];
    }
}
