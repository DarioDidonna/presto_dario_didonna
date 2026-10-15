<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=64,min_height=64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.required' => __('ui.avatar_required'),
            'avatar.image' => __('ui.validation_image_image'),
            'avatar.mimes' => __('ui.validation_image_image'),
            'avatar.max' => __('ui.avatar_max'),
            'avatar.dimensions' => __('ui.avatar_dimensions'),
        ];
    }
}
