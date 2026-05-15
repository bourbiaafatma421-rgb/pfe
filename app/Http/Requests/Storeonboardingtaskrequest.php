<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La policy est gérée dans le controller
    }

    public function rules(): array
    {
        return [
            'task_title'     => 'required|string|max:500',
            'objective'      => 'nullable|string',
            'deadline'       => 'required|date',
            'type'           => 'required|in:technique,administratif,humain,formation',
            'month_number'   => 'required|integer|min:1',
            'week_number'    => 'required|integer|min:1',
            'day_name'       => 'nullable|in:lundi,mardi,mercredi,jeudi,vendredi',
            'responsable_id' => [
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
            'task_title.required'   => 'Le titre de la tâche est obligatoire.',
            'deadline.required'     => 'La date limite est obligatoire.',
            'deadline.date'         => 'La date limite doit être une date valide.',
            'type.required'         => 'Le type de tâche est obligatoire.',
            'type.in'               => 'Le type doit être : technique, administratif, humain ou formation.',
            'month_number.required' => 'Le numéro du mois est obligatoire.',
            'week_number.required'  => 'Le numéro de semaine est obligatoire.',
            'day_name.in'           => 'Le jour doit être un jour ouvrable (lundi à vendredi).',
            'responsable_id.exists' => 'Le responsable sélectionné n\'existe pas.',
        ];
    }
}