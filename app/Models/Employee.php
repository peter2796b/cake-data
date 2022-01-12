<?php

namespace App\Models;

use Carbon\Carbon;

class Employee
{
    /**
     * Employee nae
     *
     * @var string
     */
    public string $name;

    /**
     * Date of birth
     *
     * @var Carbon
     */
    public Carbon $dateOfBirth;

    /**
     * @var int Birthday day
     */
    public int $day;

    /**
     * @var int Birthday month
     */
    public int $month;

    public function __construct($name, $dateOfBirth)
    {
        $this->name = $name;
        $this->dateOfBirth = $dateOfBirth;
        $this->day = $dateOfBirth->day;
        $this->month = $dateOfBirth->month;
    }
}
