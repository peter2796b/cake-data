<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Checks if the day is a holiday or not
     *
     * @param Carbon $date
     * @return bool
     */
    public static function IsHoliday(Carbon $date)
    {
        if (
            $date->eq(Carbon::create(null, 12, 25)) ||
            $date->isWeekend() ||
            $date->eq(Carbon::create(null, 12, 26)) ||
            $date->eq(Carbon::create(null, 01, 01)->addYear())
        ) {
            return true;
        }
        return false;
    }

    /**
     * Gets the next working day
     *
     * @param $birthdayDay
     * @return void
     */
    public static function NextWorkingDay(Carbon $birthdayDay)
    {
        do {
            $nextDay = $birthdayDay->addDay();
        } while (!$nextDay->isWeekday());

        return $nextDay;
    }

    /**
     * @param $date
     * @param $date1
     * @return bool
     */
    public static function AreConsecutiveDates(Carbon $date1, Carbon $date2)
    {
        return $date1->diffInDays($date2) == 1;
    }
}
