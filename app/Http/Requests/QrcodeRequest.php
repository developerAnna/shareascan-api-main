<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrcodeRequest extends FormRequest
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
            'hexa_color' => $this->isMethod('post')
                ? 'required|unique:qrcodes,hexa_color|regex:/^#[A-Fa-f0-9]{6}$/i'
                : '',

            'qr_data' => 'required',
        ];
    }
}
