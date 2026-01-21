<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'user_id' => 'required|exists:users,id',
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
            'title.required' => 'Le titre est obligatoire',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères',
            'content.required' => 'Le contenu est obligatoire',
            'content.min' => 'Le contenu doit contenir au moins 10 caractères',
            'user_id.required' => 'L\'utilisateur est obligatoire',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas',
            'published_at.date' => 'La date de publication doit être une date valide',
        ];
    }
}
