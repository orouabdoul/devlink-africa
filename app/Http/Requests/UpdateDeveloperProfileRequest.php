<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeveloperProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Les compétences peuvent arriver sous forme « Laravel, Vue, MySQL ».
        $skills = $this->input('skills');
        if (is_string($skills)) {
            $skills = explode(',', $skills);
        }
        $this->merge([
            'github_url' => $this->normalizeUrl($this->input('github_url')),
            'linkedin_url' => $this->normalizeUrl($this->input('linkedin_url')),
            'skills' => collect($skills ?? [])->map(fn ($s) => trim((string) $s))->filter()->unique()->values()->all(),
        ]);
    }

    /** « github.com/moi » ou « http://github.com/moi » deviennent « https://github.com/moi ». */
    private function normalizeUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        $url = preg_replace('#^http://#i', 'https://', $url);

        return preg_match('#^https://#i', $url) ? $url : 'https://'.ltrim($url, '/');
    }

    public function rules(): array
    {
        return [
            'headline' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', Rule::in(config('devlink.countries'))],
            'bio' => ['nullable', 'string', 'max:1000'],
            'skills' => ['array', 'max:15'],
            'skills.*' => ['string', 'max:30'],
            'github_url' => ['nullable', 'url:https', 'max:255', 'regex:/^https:\/\/(www\.)?github\.com\//i'],
            'linkedin_url' => ['nullable', 'url:https', 'max:255', 'regex:/^https:\/\/([a-z]{2,3}\.)?linkedin\.com\//i'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'headline' => __('titre professionnel'),
            'country' => __('pays'),
            'bio' => __('présentation'),
            'skills' => __('compétences'),
            'skills.*' => __('compétence'),
            'github_url' => __('lien GitHub'),
            'linkedin_url' => __('lien LinkedIn'),
            'avatar' => __('photo'),
        ];
    }

    public function messages(): array
    {
        return [
            'github_url.regex' => __('Le lien GitHub doit ressembler à https://github.com/votre-pseudo'),
            'linkedin_url.regex' => __('Le lien LinkedIn doit ressembler à https://www.linkedin.com/in/votre-profil'),
        ];
    }
}
