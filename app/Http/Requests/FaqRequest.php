<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
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
        $id = $this->route('faq');
        return [
            'question' => $this->isMethod('post')
                ? 'required|unique:f_a_q_s,question'
                : 'required|unique:f_a_q_s,question,' . $id,

            'answer' => 'required|string',
            'status' => 'required',
        ];
    }
}
