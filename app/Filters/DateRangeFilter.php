<?php

namespace App\Filters;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class DateRangeFilter
{
    /**
     * Normaliza fechas ISO 8601 a UTC.
     *
     * @return array{0:?string,1:?string}
     *
     * @throws ValidationException
     */
    public static function normalize(?string $from, ?string $to, string $fromField = 'dateFromInput', string $toField = 'dateToInput'): array
    {
        $from = self::normalizeSingle($from, $fromField, false);
        $to = self::normalizeSingle($to, $toField, true);

        if ($from !== null && $to !== null) {
            $fromDate = CarbonImmutable::parse($from);
            $toDate = CarbonImmutable::parse($to);

            if ($toDate->lessThan($fromDate)) {
                throw ValidationException::withMessages([
                    $toField => __('validation.after_or_equal', ['attribute' => $toField, 'date' => $fromField]),
                ]);
            }
        }

        return [$from, $to];
    }

    /**
     * @throws ValidationException
     */
    private static function normalizeSingle(?string $value, string $field, bool $endOfDayIfDateOnly): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::parse($value);

            if ($endOfDayIfDateOnly && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value)) === 1) {
                $date = $date->endOfDay();
            }

            return $date->utc()->toIso8601String();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => __('validation.date', ['attribute' => $field]),
            ]);
        }
    }
}
