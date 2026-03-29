<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
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
            'namedoc' => 'sometimes|string|max:100',
            'path' => 'sometimes|file|mimes:pdf|max:5120',
            'signature_req' => 'sometimes|boolean',
            'user_id' => 'sometimes|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'namedoc.max' => 'Le nom du document ne doit pas dépasser 100 caractères.',
            'path.mimes' => 'Le document doit être au format PDF.',
            'signature_req.boolean' => 'Le champ doit être vrai ou faux.',
            'user_id.exists' => "L'utilisateur sélectionné n'existe pas.",
        ];
    }
}
