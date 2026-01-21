<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string|min:10',
            'user_id' => 'sometimes|exists:users,id',
            'published_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères',
            'content.min' => 'Le contenu doit contenir au moins 10 caractères',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas',
            'published_at.date' => 'La date de publication doit être une date valide',
        ];
    }
}
