<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2', 'max:5000'],
            'images' => ['nullable', 'array', 'max:'.\App\Models\Attachment::MAX_PER_COMMENT],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=8000,max_height=8000'],
            'documents' => ['nullable', 'array', 'max:'.\App\Models\Attachment::MAX_DOCUMENTS_PER_COMMENT],
            'documents.*' => ['file', 'max:5120', 'mimes:'.\App\Models\Attachment::DOCUMENT_MIMES, 'extensions:'.\App\Models\Attachment::DOCUMENT_EXTENSIONS],
        ];
    }

    public function attributes(): array
    {
        return ['content' => __('réponse'), 'images' => __('captures'), 'images.*' => __('capture'), 'documents' => __('documents'), 'documents.*' => __('document')];
    }
}
