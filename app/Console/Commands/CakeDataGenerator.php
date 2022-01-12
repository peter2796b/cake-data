<?php

namespace App\Console\Commands;

use App\Helpers\DateHelper;
use App\Services\EmployeeMapperService;
use App\ViewModels\CakeDataViewModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CakeDataGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:cake-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mapper = new EmployeeMapperService('input.txt');

        $groupedByDayMonth = $this->GetDataGroupedByDayMonth($mapper->employees);

        $rawCakeData = $this->GetRawCakeData($groupedByDayMonth);
        $processedData = $this->ProcessData($rawCakeData);

        $this->WriteToFile($processedData);

    }

    /**
     * Return data grouped by date
     * @param array $employees
     * @return \Illuminate\Support\Collection
     */
    private function GetDataGroupedByDayMonth(array $employees)
    {
        return collect($employees)->sortBy(['month', 'day'], SORT_ASC)
            ->groupBy(function ($employee) {
                return $employee->dateOfBirth->format('d-m');
            });
    }

    /**
     * @param $groupedByDayMonth
     * @return CakeDataViewModel[]
     */
    private function GetRawCakeData($groupedByDayMonth)
    {
        $currentYear = Carbon::now()->year;

        $rawData = [];

        $employeeBirthdayDayMonths = $groupedByDayMonth->keys();


        foreach ($employeeBirthdayDayMonths as $birthdayDay) {
            $employeeGetsNextWorkingDayOff = false;
            $holiday = false;

            [$day, $month] = explode('-', $birthdayDay);
            $birthdayDate = Carbon::create($currentYear, $month, $day);
            $employees = $groupedByDayMonth[$birthdayDay];

            /**
             * Office closed on employees birthday
             */
            if (DateHelper::IsHoliday($birthdayDate->clone())) {
                $employeeGetsNextWorkingDayOff = true;
                $holiday = true;
            }
            /**
             * Office is closed on the following day of the birthday
             */
            if (DateHelper::IsHoliday($birthdayDate->clone()->addDay())) {
                $holiday = true;
            }

            if ($holiday) {
                // Get the next working day
                $nextWorkingDay = DateHelper::NextWorkingDay($birthdayDate->clone());
                if ($employeeGetsNextWorkingDayOff) {
                    // Employee is given an off on the working day, so get the next working day
                    $nextWorkingDay = DateHelper::NextWorkingDay($nextWorkingDay->clone());
                }
            } else {
                // Day following the birthday
                $nextWorkingDay = $birthdayDate->clone()->addDay();
            }

            $employeeNames = collect($employees)->pluck('name')->toArray();
            if (count($employees) > 1) {
                $cakeDataVM = new CakeDataViewModel($nextWorkingDay, 1, 0, $employeeNames);
            } else {
                $cakeDataVM = new CakeDataViewModel($nextWorkingDay, 0, 1, $employeeNames);
            }

            $rawData[] = $cakeDataVM;
        }

        return $rawData;
    }

    /**
     * @param CakeDataViewModel $cakeDatum1
     * @param CakeDataViewModel $cakeDatum2
     * @return CakeDataViewModel
     */
    private function CelebrateOnSameDay(CakeDataViewModel $cakeDatum1, CakeDataViewModel $cakeDatum2)
    {
        $cakeDatum1->date = $cakeDatum2->date;
        $cakeDatum1->employeeNames = array_merge($cakeDatum1->employeeNames, $cakeDatum2->employeeNames);
        $cakeDatum1->numberOfSmallCakes = 0;
        $cakeDatum1->numberOfLargeCakes = 1;

        return $cakeDatum1;
    }

    /**
     * Writes data to csv file
     * @param array $processedData
     * @return void
     */
    private function WriteToFile(array $processedData)
    {
        $filename = 'output.csv';
        $stream = fopen($filename, 'w');
        foreach ($processedData as $index => $cakeDataVM) {
            $row = [
                $cakeDataVM->date->format('d-m-Y'),
                $cakeDataVM->numberOfSmallCakes,
                $cakeDataVM->numberOfLargeCakes,
                implode(' # ', $cakeDataVM->employeeNames)
            ];
            fputcsv($stream, $row);
            dump($row[0] . ',' . $row[1] . ',' . $row[2] . ' , ' . $row[3]);
        }

        fclose($stream);

    }

    /**
     * Returns Processed data
     *
     * @param array $rawCakeData
     * @return array
     */
    private function ProcessData(array $rawCakeData)
    {
        $processedData = [];
        for ($index = 0; $index < count($rawCakeData); $index++) {
            $cakeDatum = $rawCakeData[$index];
            if ($index <= count($rawCakeData) - 2 && DateHelper::AreConsecutiveDates($cakeDatum->date->clone(), $rawCakeData[$index + 1]->date)) {
                $cakeDatum = $this->CelebrateOnSameDay($cakeDatum, $rawCakeData[$index + 1]);
                $index++;
            }
            // for last entry compare the previous date if they are consecutive
            if ($index == count($rawCakeData) - 1 && DateHelper::AreConsecutiveDates($processedData[count($processedData) - 1]->date, $cakeDatum->date)) {
                $cakeDatum->date = $cakeDatum->date->clone()->addDay();
            }
            $processedData[] = $cakeDatum;
        }
        return $processedData;
    }
}
