<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class AjoutDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'namedoc'       => 'required|string|max:100',
            'path'          => 'required|file|mimes:pdf|max:10240',
            'signature_req' => 'required|boolean',
            'user_ids'      => 'required|array|min:1',
            'user_ids.*'    => 'exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'namedoc.required'    => 'Le nom du document est requis.',
            'namedoc.max'         => 'Le nom du document ne doit pas dépasser 100 caractères.',
            'path.required'       => 'Le fichier du document est requis.',
            'path.mimes'          => 'Le document doit être au format PDF.',
            'path.max'            => 'Le fichier ne doit pas dépasser 10 MB.',
            'signature_req.required' => 'Le champ signature est requis.',
            'signature_req.boolean'  => 'Le champ doit être vrai ou faux.',
            'user_ids.required'   => 'Assignez au moins une personne.',
            'user_ids.min'        => 'Assignez au moins une personne.',
            'user_ids.*.exists'   => 'Un utilisateur sélectionné est invalide.',
        ];
    }
}