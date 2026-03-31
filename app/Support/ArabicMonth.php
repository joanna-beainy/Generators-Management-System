<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;

class ArabicMonth
{
    public static function all(bool $stringKeys = false, bool $withAllOption = false): array
    {
        $months = [
            1 => 'كانون الثاني',
            2 => 'شباط',
            3 => 'آذار',
            4 => 'نيسان',
            5 => 'أيار',
            6 => 'حزيران',
            7 => 'تموز',
            8 => 'آب',
            9 => 'أيلول',
            10 => 'تشرين الأول',
            11 => 'تشرين الثاني',
            12 => 'كانون الأول',
        ];

        if ($stringKeys) {
            $months = collect($months)
                ->mapWithKeys(fn ($name, $month) => [(string) $month => $name])
                ->all();
        }

        if ($withAllOption) {
            return ['' => 'كل الأشهر'] + $months;
        }

        return $months;
    }

    public static function name($value): string
    {
        $month = self::extractMonth($value);

        if (!$month) {
            return '';
        }

        return self::all()[$month] ?? '';
    }

    public static function label($value, ?int $year = null): string
    {
        if ($value instanceof DateTimeInterface || $value instanceof CarbonInterface) {
            $monthName = self::name($value);
            $resolvedYear = $value->format('Y');

            return trim("{$monthName} {$resolvedYear}");
        }

        $monthName = self::name($value);

        return trim($year ? "{$monthName} {$year}" : $monthName);
    }

    private static function extractMonth($value): ?int
    {
        if ($value instanceof DateTimeInterface || $value instanceof CarbonInterface) {
            return (int) $value->format('n');
        }

        if (is_numeric($value)) {
            $month = (int) $value;

            return $month >= 1 && $month <= 12 ? $month : null;
        }

        return null;
    }
}
