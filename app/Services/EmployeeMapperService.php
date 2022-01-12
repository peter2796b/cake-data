<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class EmployeeMapperService
{
    /**
     * @var array Employee
     */
    public array $employees = [];

    public function __construct($fileName)
    {
        $contents = Storage::get($fileName);
        $this->MapContentsToEmployees($contents);
    }

    /**
     * Maps the string content to the employees array
     * @param string $contents
     * @return void
     */
    private function MapContentsToEmployees(string $contents)
    {
        $rows = explode(PHP_EOL, $contents);
        foreach ($rows as $row) {
            if (strlen($row) == 0) {
                continue;
            }
            [$name, $dateOfBirth] = explode(',', $row);
            $carbonDate = Carbon::parseFromLocale($dateOfBirth);
            $this->employees[] = new Employee($name, $carbonDate);
        }
    }
}
