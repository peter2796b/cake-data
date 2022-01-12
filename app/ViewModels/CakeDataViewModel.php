<?php

namespace App\ViewModels;

use Carbon\Carbon;

class CakeDataViewModel
{
    /**
     * @var Carbon
     */
    public Carbon $date;

    /**
     * @var integer
     */
    public int $numberOfSmallCakes;

    /**
     * @var integer
     */
    public int $numberOfLargeCakes;

    /**
     * @var array string
     */
    public array $employeeNames = [];

    /**
     * @param Carbon $date
     * @param int $numberOfLargeCakes
     * @param int $numberOfSmallCakes
     * @param $employeeNames
     */
    public function __construct(Carbon $date, int $numberOfLargeCakes, int $numberOfSmallCakes, $employeeNames)
    {
        $this->date = $date;
        $this->numberOfLargeCakes = $numberOfLargeCakes;
        $this->numberOfSmallCakes = $numberOfSmallCakes;
        $this->employeeNames = $employeeNames;
    }
}
