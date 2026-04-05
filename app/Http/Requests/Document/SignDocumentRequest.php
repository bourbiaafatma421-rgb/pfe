<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class SignDocumentRequest extends FormRequest
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
            'document_id' => 'required|exists:documents,id', // ID du document à signer
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:1024', // Image de la signature graphique
        ];
    }
    public function messages(): array
    {
        return [
            'document_id.required' => 'Le document à signer est requis.',
            'document_id.exists' => 'Le document sélectionné est invalide.',
            'signature.required' => 'Vous devez fournir une signature graphique.',
            'signature.image' => 'La signature doit être une image.',
            'signature.mimes' => 'La signature doit être au format png, jpg ou jpeg.',
            'signature.max' => 'La signature ne doit pas dépasser 1 Mo.',
        ];
    }

}
