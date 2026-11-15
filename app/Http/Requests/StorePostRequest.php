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
            'images' => ['nullable', 'array', 'max:'.\App\Models\Attachment::MAX_PER_POST],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=8000,max_height=8000'],
            'documents' => ['nullable', 'array', 'max:'.\App\Models\Attachment::MAX_DOCUMENTS_PER_POST],
            'documents.*' => ['file', 'max:5120', 'mimes:'.\App\Models\Attachment::DOCUMENT_MIMES, 'extensions:'.\App\Models\Attachment::DOCUMENT_EXTENSIONS],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => __('titre'),
            'content' => __('contenu'),
            'type' => __('type de publication'),
            'category_id' => __('catégorie'),
            'images' => __('captures'),
            'images.*' => __('capture'),
            'documents' => __('documents'),
            'documents.*' => __('document'),
        ];
    }
}
