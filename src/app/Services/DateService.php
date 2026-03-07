<?php

namespace App\Services;

use Carbon\Carbon;

class DateService
{
    public function resolveDate(?string $date): Carbon
    {
        return $date
            ? Carbon::createFromFormat('Y-m-d', $date)
            : Carbon::today();
    }

    public function resolveMonth(?string $month): Carbon
    {
        return $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();
    }

    public function getMonthRange(Carbon $month): array
    {
        return [
            $month->copy()->startOfMonth(),
            $month->copy()->endOfMonth(),
        ];
    }

    public function getPreviousNextDates(Carbon $date): array
    {
        return [
            'previous' => $date->copy()->subDay(),
            'next'     => $date->copy()->addDay(),
        ];
    }

    public function getPreviousNextMonths(Carbon $month): array
    {
        $month = $month->copy()->startOfMonth();

        return [
            'previous' => $month->copy()->subMonth(),
            'next'     => $month->copy()->addMonth(),
        ];
    }

    public function getDatesInMonth(Carbon $start, Carbon $end)
    {
        $dates = [];

        $date = $start->copy();

        while ($date <= $end) {
            $dates[] = $date->copy();
            $date->addDay();
        }

        return $dates;
    }
}