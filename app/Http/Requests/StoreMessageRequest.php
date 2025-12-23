<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone_number' => 'required|string|regex:/^\+[1-9]\d{1,14}$/',
            'content' => 'required|string|max:160',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required',
            'phone_number.regex' => 'Phone number must be in E.164 format (e.g., +905551111111)',
            'content.required' => 'Message content is required',
            'content.max' => 'Message content cannot exceed 160 characters',
        ];
    }
}
