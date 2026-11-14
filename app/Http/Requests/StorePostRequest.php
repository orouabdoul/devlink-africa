<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // L'appartenance est vérifiée par PostPolicy dans le contrôleur.
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:8', 'max:180'],
            'content' => ['required', 'string', 'min:20', 'max:20000'],
            'type' => ['required', Rule::in(array_keys(Post::TYPES))],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => __('titre'),
            'content' => __('contenu'),
            'type' => __('type de publication'),
            'category_id' => __('catégorie'),
        ];
    }
}
