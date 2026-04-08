<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post
            ? ($this->user()?->can('update', $post) ?? false)
            : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:8', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'summary' => ['required', 'string', 'min:20', 'max:280'],
            'body' => ['required', 'string', 'min:80'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_public' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please add a title for your question.',
            'title.min' => 'Make the title at least 8 characters so it is clear.',
            'category_id.required' => 'Choose a category to organize your post.',
            'summary.required' => 'Add a short summary so readers can scan quickly.',
            'summary.min' => 'Summary should be at least 20 characters.',
            'body.required' => 'Please write the main explanation.',
            'body.min' => 'Detailed explanation should be at least 80 characters.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'Use JPG, JPEG, PNG, or WEBP format.',
            'image.max' => 'Image must be 4MB or smaller.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_public' => $this->boolean('is_public'),
            'published_at' => $this->filled('published_at') ? $this->input('published_at') : null,
        ]);
    }
}
