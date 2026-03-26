<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ErrorCodeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'errorCodeId' => $this->input('errorCodeId', $this->route('id')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'errorCodeId' => ['nullable', 'integer', 'exists:error_codes,id'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('error_codes')
                    ->where('application_id', $this->application_id)
                    ->ignore($this->errorCodeId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'string', 'max:255'],
            'line' => ['nullable', 'integer', 'min:1'],
            'severity' => ['nullable', Rule::in(['critical', 'high', 'medium', 'low', 'other'])],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'application_id.required' => __('error_codes.validation.application_id_required'),
            'application_id.exists' => __('error_codes.validation.application_id_invalid'),
            'code.required' => __('error_codes.validation.code_required'),
            'code.max' => __('error_codes.validation.code_max'),
            'code.unique' => __('error_codes.validation.code_unique'),
            'name.required' => __('error_codes.validation.name_required'),
            'name.max' => __('error_codes.validation.name_max'),
            'file.max' => __('error_codes.validation.file_max'),
            'line.integer' => __('error_codes.validation.line_integer'),
            'line.min' => __('error_codes.validation.line_min'),
            'severity.in' => __('error_codes.validation.severity_invalid'),
        ];
    }
}
