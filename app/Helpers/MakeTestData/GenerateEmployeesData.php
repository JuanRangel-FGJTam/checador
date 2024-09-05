<?php

namespace App\Helpers\MakeTestData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\{
    DailyRecord,
    Employee,
    Record,
    WorkingDays,
    WorkingHours
};
use Faker\Factory as Faker;

class GenerateEmployeesData {

    public static function makeNewEmployeesWithRecords(){
        $faker = Faker::create();
    
        $general_directions_id = 2;
        $direction = 2;
        $subdirections = [2,3,4];
    
        $employees = array();
    
        for ($i=0; $i < 66; $i++) {
            
            $_name = $faker->name();
            $_urlAvatar = sprintf("https://ui-avatars.com/api/?name=%s&background=234551&color=eee", str_replace(" ", "+", $_name));

            //* Create employee
            $employee = Employee::create([
                'general_direction_id' => $general_directions_id,
                'direction_id' => $direction,
                'subdirectorate_id' => $faker->randomElement($subdirections),
                'department_id' => 1,
                'name' => $_name,
                'photo' => $_urlAvatar,
                "plantilla_id" => "9" . $faker->randomNumber(8),
                "active" => 1,
                "status_id" => 1
            ]);
    
            $inWeek = $faker->randomElement([1,1,1,0]);
            $workingDays = WorkingDays::create([
                'employee_id' => $employee->id,
                'week' => $inWeek,
                'weekend' => $inWeek == 1 ?0:1,
                'holidays' => 0
            ]);
    
    
            $workingH = WorkingHours::create([
                'employee_id' => $employee->id,
                'checkin' => "09:00",
                'checkout' => "17:00"
            ]);
    
            // * create checkin record
            for ($j=1; $j <= 30; $j++) {
                Record::create([
                    'employee_id' => $employee->id,
                    'check' => sprintf("2024-08-%s ", str_pad($j, 2, '0', STR_PAD_LEFT) ) . $faker->dateTimeBetween('09:00 -25 minutes', '09:00 +25 minutes')->format('H:i:s')
                ]);
                Record::create([
                    'employee_id' => $employee->id,
                    'check' => sprintf("2024-08-%s ", str_pad($j, 2, '0', STR_PAD_LEFT) ) . $faker->dateTimeBetween('17:00 -25 minutes', '17:00 +25 minutes')->format('H:i:s')
                ]);
            }
    
            Log::info("Employee '{id}':'{name}' created", [
                "id" => $employee->id,
                "name" => $employee->name
            ]);
            array_push( $employees, $employee);
        }

    }

    public static function makeRecordsOfMonth(int $month, int $generalDirectionID, int $directionID = 0 ){
        $faker = Faker::create();

        $year = 2024;
    

        // * retrive the employees
        $employeesQuery = Employee::where('general_direction_id', $generalDirectionID);
        if( $directionID > 0){
            $employeesQuery->where('direction_id', $directionID);
        }

        // * loop each
        $employees = $employeesQuery->get();
        foreach ($employees as $employee) {

            $daysInMonth = Carbon::parse("$year-$month-01")->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                Record::create([
                    'employee_id' => $employee->id,
                    'check' => sprintf( "2024-%s-%s ",str_pad($month, 2, '0', STR_PAD_LEFT), str_pad($i, 2, '0', STR_PAD_LEFT) ) . $faker->dateTimeBetween('09:00 -25 minutes', '09:00 +25 minutes')->format('H:i:s')
                ]);
                Record::create([
                    'employee_id' => $employee->id,
                    'check' => sprintf("2024-%s-%s ", str_pad($month, 2, '0', STR_PAD_LEFT), str_pad($i, 2, '0', STR_PAD_LEFT) ) . $faker->dateTimeBetween('17:00 -25 minutes', '17:00 +25 minutes')->format('H:i:s')
                ]);
            }

            Log::info("Added record to employee '{id}':'{name}'", [
                "id" => $employee->id,
                "name" => $employee->name
            ]);

        }

    }

}