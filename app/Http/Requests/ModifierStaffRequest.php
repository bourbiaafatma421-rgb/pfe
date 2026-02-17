<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModifierStaffRequest extends FormRequest
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
        $currentUser = auth()->user();

        // Tous les champs autorisés pour Manager et RH
        $rules = [
            'first_name' => 'sometimes|string|max:25',
            'last_name' => 'sometimes|string|max:25',
            'phone_number' => ['sometimes','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
            'date_of_hire' => 'sometimes|date',
        ];

        return $rules;
    }
}
