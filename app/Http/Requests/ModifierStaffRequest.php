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
        $staff = \App\Models\Staff::find($this->route('id'));
        $userId = $staff ? $staff->user_id : null;
        return [
            'email' => 'required|email|unique:users,email,' . $userId,
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'role' => 'required|in:rh,MANAGER',
            'active' => 'nullable|boolean'
        ];
    }
}
