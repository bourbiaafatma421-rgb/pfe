<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->deadline) {
            $this->merge([
                'deadline' => \Carbon\Carbon::parse($this->deadline)->toDateString(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'task_title'       => 'sometimes|string|max:500',
            'objective'        => 'sometimes|nullable|string',
            'deadline'         => 'sometimes|date',
            'type'             => ['sometimes', 'in:technique,administratif,humain,formation'],
            'status'           => 'sometimes|in:en_attente,en_cours,en_validation,rejetee,termine',
            'rejection_reason' => 'sometimes|nullable|string|max:1000',
            'responsable_id'   => [
                'sometimes',
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $user = \App\Models\User::find($value);
                        if (!$user || $user->role === 'new_collaborateur') {
                            $fail('Le responsable ne peut pas être un nouveau collaborateur.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'deadline.date'         => 'La date limite doit être une date valide.',
            'type.in'               => 'Le type doit être : technique, administratif, humain ou formation.',
            'status.in'             => 'Statut invalide.',
            'responsable_id.exists' => 'Le responsable sélectionné n\'existe pas.',
        ];
    }
}