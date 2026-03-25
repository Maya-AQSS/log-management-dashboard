<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DateRangeFilterRequest extends FormRequest
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
        return self::rulesFor('date_from', 'date_to');
    }

    /**
     * Reusable ISO 8601 date-range rules for list filters.
     *
     * @return array<string, array<int, string>>
     */
    public static function rulesFor(string $fromField, string $toField): array
    {
        return [
            $fromField => ['nullable', 'date_format:c'],
            $toField => ['nullable', 'date_format:c', 'after_or_equal:' . $fromField],
        ];
    }
}

