<?php

namespace App\Helpers;

use DateTime;
use Carbon\Carbon;
use App\Models\{
    Employee,
    Justify,
    WorkingHours,
    Record
};

class EmployeeKardexRecords {

    protected $justifiesNoAllDayIds = array(1, 12, 13, 14, 15, 16, 18, 19, 20);
    protected $greenColor = '73c219';
    protected $redColor = 'eeb0b0';
    protected $redColorFalta = 'd36161';
    protected $yellowColor = 'f1f086';
    protected $toleranceTime = 10;
    protected $foulTime = 20;

    protected Employee $employee;
    protected bool $checaComida = false;
    protected $toCheckout;
    protected WorkingHours $workingHours;

    function __construct(Employee $employee)
    {
        $this->employee = $employee;
        $this->employee->load('workingHours');

        // * manually get the working hours
        $this->workingHours = WorkingHours::where('employee_id', $employee['id'])->first();

        if ($this->workingHours->toeat && $this->workingHours->toarrive) {
            $this->checaComida = true;
        }

        $this->toCheckout = Carbon::parse($this->workingHours->checkout);
    }
    
    /**
     * makeRecords
     *
     * @return mixed
     */
    public function makeRecords(int $year){
        
        $month = 12;
        $initMonth = 1;
        
        $today = new DateTime();
        // if ($year == $today->format('Y')) {
        //     $month = $today->format('m');
        // }

        // if ($year == 2021) { // start project on july 2021
        //     $initMonth = 7;
        // }

        // * loop throght the months
        for ($currentMonth = $initMonth; $currentMonth <= $month; $currentMonth++) { // month by month
            
            // * prepare the arrays
            $checadas = array();
            
            // * firts check if employee has justify days in the month
            $justifiedDays = $this->retriveEmployeeJustifications($year, $currentMonth);

            // * Get the first and last day of the month
            $startOfMonth = Carbon::create($year, $currentMonth, 1);
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            // * loop through each day in the month
            for ($currentDay = $startOfMonth; $currentDay->lte($endOfMonth); $currentDay->addDay()) {
                if( $currentDay->format('Ymd') > $today->format('Ymd')){
                    break; // exit of the lpop
                }

                array_push( $checadas, $this->employeeRecordDay($currentDay, $justifiedDays));
            }

            $months[] = array(
                'name' => self::monthsName($currentMonth),
                'checadas' => $checadas
            );
        }


        // * prepare data
        $dataEmployee = array(
            'name' => $this->employee->name,
            'checaComida' => $this->checaComida,
            'months' => $months
        );


        // * attemot to stote the data on mongodb as cache
        try {
            $recordMongo = \App\Models\KardexRecord::where('employee_id', $this->employee->id)
                ->where('year', $year)
                ->where('report_date', '=', $today->format('Y-m-d'))
                ->first();
            if (!$recordMongo) {
                $recordMongo = new \App\Models\KardexRecord();
            }

            // * store in mongoDB
            $recordMongo->employee_id = $this->employee->id;
            $recordMongo->report_date = $today->format('Y-m-d');
            $recordMongo->year = $year;
            $recordMongo->data = $dataEmployee;
            $recordMongo->save();
        } catch (\Throwable $th) {
            Log:error($th->getMessage());
        }

        return $dataEmployee;
    }
    
    /**
     * retrive employee justifications
     *
     * @param  int $year
     * @param  int $month
     * @return array
     */
    private function retriveEmployeeJustifications(int $year, int $month){
        $justifiedDays = array();

        $justifies = Justify::where('employee_id', $this->employee->id)
            ->whereYear('date_start', $year)
            ->whereMonth('date_start', $month)
            ->get();

        foreach($justifies as $justify) {
            $dateInitJ = new \DateTime($justify->date_start);
            if ($justify->date_finish) {
                $dateFinishJ = new \DateTime($justify->date_finish);
                for ($k = $dateInitJ->format('j'); $k <= $dateFinishJ->format('j') ; $k++) {
                    $justifiedDays[$k] = array(
                        'name' => $justify->type->name,
                        'justify_id' => $justify->id
                    );
                }
            } else {
                $justifiedDays[$dateInitJ->format('j')] = array(
                    'name' => $justify->type->name,
                    'justify_id' => $justify->type_justify_id
                );
            }
        }

        return $justifiedDays;
    }

    /**
     * employeeRecordDay
     *
     * @return array
     */
    private function employeeRecordDay($currentDay, $justifiedDays){

        // Init values
        $checkin = 'F';
        $checkout = 'F';
        $eat = '';
        $arrive = '';
        $bgCheckin = '';
        $bgEat = '';
        $bgArrive = '';
        $bgCheckout = '';
        $records = array();
        $showRecords = true;

        if($this->checaComida) {
            $eat = 'F';
            $arrive = 'F';
        }

        // * check if the current day is weekend
        if($currentDay->format('N') == 6 || $currentDay->format('N') == 7) { // Except weekends
            $checkin = '';
            $checkout = '';
            $eat = '';
            $arrive = '';
        }

        // * verify if the day was justified
        if(isset($justifiedDays[$currentDay->day])){
            $justification = $justifiedDays[$currentDay->day];
            // * Justified day
            $bgCheckin = $this->greenColor;
            $bgArrive = $this->greenColor;
            $bgCheckout = $this->greenColor;
            $checkin = $justification['name'];
            $checkout = $justification['name'];
            $eat = $justification['name'];
            $arrive = $justification['name'];
            $showRecords = false;
            
            // * justificaciones que no son de dia completo
            if (in_array($justification['justify_id'], $this->justifiesNoAllDayIds)) {
                $showRecords = true;
                $checkin = 'F';
                $checkout = 'F';
                $eat = '';
                $arrive = '';
                $bgCheckin = '';
                $bgCheckout = '';
                $bgArrive = '';
            }
        }

        if ($showRecords) {
            
            // * get checkin
            $records = Record::select('check')
                ->where('employee_id', $this->employee->id)
                ->whereDate('check', $currentDay->format('Y-m-d'))
                ->get();

            // Horario corrido
            $hour1 = strtotime($this->workingHours->checkin);
            $hour2 = strtotime($this->workingHours->checkout);

            // horario quebrado
            $hour3 = strtotime($this->workingHours->toeat);
            $hour4 = strtotime($this->workingHours->toarrive);
            
            // * loop through each check record
            $recordsArray = [];
            foreach ($records as $record) {
                $dateRecord = new \DateTime($record->check);
                $timeRecord = $dateRecord->format('H:i');

                if (!in_array($timeRecord, $recordsArray))
                {
                    $recordsArray[] = $timeRecord;
                }
            } // End foreach

            foreach ($recordsArray as $timeRecord) {
                $diffCheckin = round(abs(strtotime($timeRecord) - $hour1) / 3600, 2);
                $diffCheckout = round(abs(strtotime($timeRecord) - $hour2) / 3600, 2);

                $diffToEat = round(abs(strtotime($timeRecord) - $hour3) / 3600, 2);
                $diffToArrive = round(abs(strtotime($timeRecord) - $hour4) / 3600, 2);

                if (!$this->checaComida) {
                    if ($diffCheckin < $diffCheckout && $checkin == 'F') {
                        $checkin = $timeRecord;
                    } elseif ($checkout) {
                        $checkout = $timeRecord;
                    }
                } else {
                    if ($diffCheckin < $diffToEat && $checkin == 'F') {
                        $checkin = $timeRecord;
                    } elseif ($diffToEat < $diffToArrive) {
                        $eat = $timeRecord;
                    } elseif ($diffToArrive < $diffCheckout) {
                        $arrive = $timeRecord;
                    } elseif ($checkout) {
                        $checkout = $timeRecord;
                    }
                }
            }

            // retardos en la entrada
            if ($checkin != '' && $checkin != 'F') {
                // Sacar retardos
                $hour1 = new \DateTime($this->workingHours->checkin);
                $hour2 = new \DateTime($this->workingHours->checkin);
                $retardo = $hour1->modify("+$this->toleranceTime minutes");
                $falta = $hour2->modify("+$this->foulTime minutes");

                if ($checkin > $retardo->format('H:i') && $checkin <= $falta->format('H:i')) {
                    $bgCheckin = $this->yellowColor; // Yellow
                } elseif ($checkin > $falta->format('H:i')) {
                    $bgCheckin = $this->redColor; // Red
                }
            }

            // Tarde de comer?
            if ($arrive != '' && $arrive != 'F') {
                $hour1 = new \DateTime($this->workingHours->toarrive);
                $hour2 = new \DateTime($this->workingHours->toarrive);
                $retardo2 = $hour1->modify("+$this->toleranceTime minutes");
                $falta2 = $hour2->modify("+$this->foulTime minutes");
                if ($arrive > $retardo2->format('H:i') && $arrive <= $falta2->format('H:i')) {
                    $bgArrive = $this->yellowColor; // Yellow
                } elseif ($arrive > $falta2->format('H:i')) {
                    $bgArrive = $this->redColor; // Red
                }
            }

            // Se fue temprano?
            if ($checkout != '' && $checkout != 'F') {
                if ($checkout < $this->toCheckout->format('H:i')) {
                    $bgCheckout = $this->redColor;
                }
            }

            // bg red Faltas
            if ($checkin === 'F') {
                $bgCheckin = $this->redColorFalta;
            }
            if ($checkout === 'F') {
                $bgCheckout = $this->redColorFalta;
            }
            if ($arrive === 'F') {
                $bgArrive = $this->redColorFalta;
            }
            if ($eat === 'F') {
                $bgEat = $this->redColorFalta;
            }

        } // End Show Records

        // Sacar justificaciones que no son de dias completos
        if (isset($justifiedDays[$currentDay->day])) {
            $justification = $justifiedDays[$currentDay->day];
            if (in_array($justification['justify_id'], $this->justifiesNoAllDayIds)) {
                // Omision de entrada, retardo menor, retardo mayor
                if ($justification['justify_id'] == 15 || $justification['justify_id'] == 19 || $justification['justify_id'] == 20) {
                    $checkin =  $justification['name'];
                    $bgCheckin = $this->greenColor;
                } elseif($justification['justify_id'] == 16) { // Omision de salida
                    $checkout = $justification['name'];
                    $bgCheckout = $this->greenColor;
                } else {
                    if ($checkin != '' && $checkin != 'F') {
                        $checkout = $justification['name'];
                        $bgCheckout = $this->greenColor;
                    } else {
                        $bgCheckin = $this->greenColor;
                        $checkin = $justification['name'];
                    }
                }
            }
        }

        return array(
            'dayName' => $this->translateDayName( $currentDay->format('D')),
            'day' => $currentDay->day,
            'checkin' => $checkin,
            'eat' => $eat,
            'arrive' => $arrive,
            'checkout' => $checkout,
            'bgCheckin' => $bgCheckin,
            'bgEat' => $bgEat,
            'bgArrive' => $bgArrive,
            'bgCheckout' => $bgCheckout,
            'total' => count($records)
        );

    }

    
    private function monthsName($month) {
        $names = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return $names[$month - 1];
    }

    private function translateDayName($name) {
        $days['Mon'] = 'Lun';
        $days['Tue'] = 'Mar';
        $days['Wed'] = 'Mie';
        $days['Thu'] = 'Jue';
        $days['Fri'] = 'Vie';
        $days['Sat'] = 'Sab';
        $days['Sun'] = 'Dom';

        return $days[$name];
    }

}