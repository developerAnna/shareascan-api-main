<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
        $id = $this->route('category');
        return [
            'category_id' => $this->isMethod('post')
                ? 'required|unique:categories,category_id'
                : 'required|unique:categories,category_id,' . $id,

            // Validate description: required and must be a string for both POST and PUT
            'description' => 'required|string',

            // Validate image:
            'image' => $this->isMethod('post') // On POST, image is required
                ? 'required|mimes:jpg,jpeg,png,gif,svg,webp|max:2048' // Validate file types and size limit for create
                : 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:2048', // On PUT, image is optional, but validate if provided
        ];
    }

    public function messages()
    {
        return [
            'category_id.required' => 'The category field is required.',
        ];
    }
}
