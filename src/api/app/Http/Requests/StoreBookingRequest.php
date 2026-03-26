<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resource_id' => ['required', 'integer', 'exists:resources,id'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s', 'before:end_at'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
            'customer_name' => ['required', 'string', 'max:255'],
        ];
    }
}
