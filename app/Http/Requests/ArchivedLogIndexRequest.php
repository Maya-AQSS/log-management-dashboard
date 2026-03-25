<?php

namespace App\Http\Requests;

use App\Enums\Severity;
use Illuminate\Foundation\Http\FormRequest;

class ArchivedLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'severity' => ['nullable', Severity::validationRule()],
            'tutorial' => ['nullable', 'in:with_tutorial,without_tutorial'],
        ];
    }
}
