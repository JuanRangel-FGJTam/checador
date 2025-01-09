<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\{
    Incident,
    Justify,
    Record,
    WorkingHours
};
use DateInterval;
use DateTime;

class IncidentService
{
    public DateTime $date;
    public int $employee_id;
    public WorkingHours $working_hours;

    const STATE_PENDIENTE_ID = 1;

    const FALTA_ID = 1;
    const FALTA_COMIDA_ID = 10;

    const RETARDO_ID = 2;
    const RETARDO_MAYOR_ID = 3;
    const RETARDO_ENTRADA_COMIDA_ID = 6;

    const OMISION_ENTRADA_ID = 4;
    const OMISION_ENTRADA_COMIDA_ID = 7;
    const OMISION_SALIDA_ID = 5;
    const OMISION_SALIDA_COMIDA_ID = 8;

    const SALIDA_ANTES_COMIDA_ID = 9;
    const SALIDA_ANTES_ID = 11;

    const INCIDENT_METHOD_MAP = [
        'checkin' => [
            'Retardo Menor' => 'storeRetardoMenor',
            'Retardo Mayor' => 'storeRetardoMayor',
            'Falta' => 'storeOmisionEntrada',
        ],
        'toeat' => [
            'Falta' => 'storeOmisionSalidaComida',
            'Salio Antes' => 'storeSalioAntesComida',
        ],
        'toarrive' => [
            'Falta' => 'storeOmisionEntradaComida',
            'Retardo Mayor' => 'storeRetardoComida',
        ],
        'checkout' => [
            'Falta' => 'storeOmisionSalida',
            'Salio Antes' => 'storeSalioAntes',
        ],
    ];

    const JUSTIFICA_OMISION_ENTRADA_IDS = [
        15, // Justificación de omisión de entrada
        27, // Justificación de entrada por falla del Sistema
    ];
    const JUSTIFICA_OMISION_SALIDA_IDS = [
        16, // Justificación de omisión de salida
        28, // Justificación de salida por falla del Sistema
    ];
    const JUSTIFICA_RETARDO_MENOR_ID = 19;
    const JUSTIFICA_RETARDO_MAYOR_ID = 20;
    const JUSTIFICA_DIAS_IDS = [
        1, // Justificacion falla del sistema
        2, // Suspension por sancion diciplinaria
        3, // Permiso economico
        4, // Permiso por lactancia
        5, // Permiso por guarderia
        6, // Permiso por cuidados maternos
        7, // Permiso por fallecimiento
        8, // Incapacidad del IMSS
        9, // Permiso sin goce de sueldo
        10, // Periodo vacacional
        11, // Vacaciones extemporaneas
        12, // Comisión oficial
        13, // Evento sindical
        14, // Motivos de salud (Médico)
        15, // Motivos de salud (Dentista)
        17, // Justificación de día completo
        18, //Curso y/o Capacitación,
        21, // Incapacidad del ISSSTE
        22, // Incapacidad médico particular
        23, // Incapacidad Hospital General
        24, // Periodo de maternidad
        25, // Día de descanso obligatorio (Oficial)
        26, // Día de descanso no obligatorio (Autorizado)
    ];

    /**
     * __construct
     *
     * @param  int $employee_id
     * @param  WorkingHours $working_hours
     * @param  DateTime|string $date
     * @return void
     */
    public function __construct($employee_id, $working_hours, $date)
    {
        $this->date = ($date instanceof DateTime) ? $date : new DateTime($date);
        $this->employee_id = $employee_id;
        $this->working_hours = $working_hours;
    }

    private function storeIncident($typeId) : Incident
    {
        // * attempt to get the records of the day
        $record = Record::whereDate('check', $this->date)
            ->where('employee_id', $this->employee_id)
            ->first();
        
        $incident = new Incident();
        $incident->employee_id = $this->employee_id;
        $incident->incident_type_id = $typeId;
        $incident->incident_state_id = self::STATE_PENDIENTE_ID;
        $incident->date = $this->date;
        $incident->save();

        return $incident;
    }

    public function storeFalta()
    {
        return $this->storeIncident(self::FALTA_ID);
    }

    public function storeFaltaComida()
    {
        return $this->storeIncident(self::FALTA_COMIDA_ID);
    }

    public function storeRetardoMenor()
    {
        return $this->storeIncident(self::RETARDO_ID);
    }

    public function storeRetardoMayor()
    {
        return $this->storeIncident(self::RETARDO_MAYOR_ID);
    }

    public function storeRetardoComida()
    {
        return $this->storeIncident(self::RETARDO_ENTRADA_COMIDA_ID);
    }

    public function storeOmisionEntrada()
    {
        return $this->storeIncident(self::OMISION_ENTRADA_ID);
    }

    public function storeOmisionEntradaComida()
    {
        return $this->storeIncident(self::OMISION_ENTRADA_COMIDA_ID);
    }

    public function storeOmisionSalida()
    {
        return $this->storeIncident(self::OMISION_SALIDA_ID);
    }

    public function storeOmisionSalidaComida()
    {
        return $this->storeIncident(self::OMISION_SALIDA_COMIDA_ID);
    }

    public function storeSalioAntes()
    {
        return $this->storeIncident(self::SALIDA_ANTES_ID);
    }

    public function storeSalioAntesComida()
    {
        return $this->storeIncident(self::SALIDA_ANTES_COMIDA_ID);
    }

    public function deleteAllByDay()
    {
        $incidents = Incident::where('employee_id', $this->employee_id)
            ->where('date', $this->date)
            ->get();

        foreach ($incidents as $incident) {
            $incident->delete();
        }

        return true;
    }

    /**
     * Assess incidents based on employee records and working hours.
     *
     * @param object $workingHours An object containing working hours.
     * @param array $records Array of filtered attendance records.
     * @param array $types  Array of type of records.
     * @return array An array containing assessments for each record.
     */
    public function assessIncidents($workingHours, $records): ?array 
    {
        $results = [];
        $types = ['checkin', 'checkout'];

        if ($workingHours->toeat && $workingHours->toarrive) {
            array_push($types, 'toeat', 'toarrive');
        }

        $recordMarkers = array_fill_keys($types, null); // Initialize all markers as null

        foreach ($types as $type) {
            foreach ($records as $record) {
                $checkTime = new Carbon($record['check']);
                $checkTime->setSecond(0)->setMillisecond(0);
                $scheduledTime = new Carbon($checkTime->format('Y-m-d') . ' ' . $workingHours->$type);
                $diffInMinutes = $scheduledTime->diffInMinutes($checkTime, false);

                // Update the marker if not set or find the most appropriate record for each type
                if (is_null($recordMarkers[$type]) || $this->isMoreAppropriateRecord($diffInMinutes, $recordMarkers[$type]['difference'])) {
                    $recordMarkers[$type] = [
                        'type' => $type,
                        'incident' => $this->determineIncident($type, $diffInMinutes),
                        'time' => $checkTime->toTimeString(),
                        'scheduled' => $scheduledTime->toTimeString(),
                        'difference' => $diffInMinutes
                    ];
                }
            }
        }

        // Compile the final results
        foreach ($recordMarkers as $data) {
            $results[] = $data;
        }

        return $results;
    }

    /**
     * Assess incidents based on employee records and working hours.
     *
     * @param object $workingHours An object containing working hours.
     * @param array $matchingRecord
     * @return array An array containing assessments for each record.
     */
    public function assessIncidentsV2($workingHours, $matchingRecord): ?array
    {
        $results = [];
        $types = ['checkin', 'checkout'];

        if($workingHours->toeat && $workingHours->toarrive)
        {
            $types = ['checkin', 'toeat', 'toarrive', 'checkout'];
        }

        // * initialize all markers as null
        $recordMarkers = array_fill_keys($types, null);

        for ($i = 0; $i < count($matchingRecord); $i++)
        {
            /** @var array $record */
            $record = $matchingRecord[$i];
            $currentType = $types[$i];
            $diffInMinutes = 0;
            $incident = "";

            if($record[1] == null)
            {
                $diffInMinutes = 9999;
                $incident = 'Falta';
            }
            else
            {
                // * calculate the incident
                $scheduleDate = Carbon::parse($record[0])->setSecond(0)->setMillisecond(0);
                $checkDate = Carbon::parse($record[1]->check)->setSecond(0)->setMillisecond(0);
                $diffInMinutes = $scheduleDate->diffInMinutes($checkDate);
                $incident = $this->determineIncident($types[$i], $diffInMinutes);
            }

            // * create the marker
            $recordMarkers[$currentType] = [
                'type' => $currentType,
                'incident' => $incident,
                'time' => isset($record[1]) ? Carbon::parse($record[1]->check)->format('Y-m-d H:i:s') : null,
                'scheduled' => $record[0]->format('Y-m-d H:i:s'),
                'difference' => $diffInMinutes
            ];

        }

        // * compile the final results
        foreach ($recordMarkers as $data)
        {
            $results[] = $data;
        }
        return $results;
    }

    public function calculateAndStoreIncidents()
    {
        $isDateJustified = $this->isDateJustified();
        $records = $this->removeDuplicateRecordsV2();

        // * delete previous incidents if exists
        $this->deleteAllByDay();

        Log::debug("Justification for employee {employeeId} of the data {date}: {justificationsId}", [
            "employeeId" => $this->employee_id,
            "date" => $this->date,
            "justificationsId" => $isDateJustified
        ]);

        if ($records->isEmpty())
        {
            if (!$isDateJustified)
            {
                $this->storeFalta();
                Log::debug("Store the incident 'falta' (Condition A)");
            }
            elseif ( empty(array_intersect(self::JUSTIFICA_DIAS_IDS, $isDateJustified)) )
            {
                $this->storeFalta();
                Log::debug("Store the incident 'falta' (Condition B)");
            }
            else
            {
                Log::debug("Not store the incident 'falta', the day is justifed");
            }
            return True;
        }

        $assessment = $this->assessIncidents($this->working_hours, $records);
        foreach ($assessment as $row)
        {
            if ($row['incident'] != 'A Tiempo')
            {
                if (!$isDateJustified || !$this->isIncidentJustified($row, $isDateJustified))
                {
                    $this->{$this->getIncidentMethod($row)}();
                }
            }
        }

        return True;
    }

    public function calculateAndStoreIncidentsV2()
    {
        $isDateJustified = $this->isDateJustified();

        // * delete previous incidents if exists
        $this->deleteAllByDay();

        Log::debug("Beggin calculate incidents for employee {employeeId} of the data {date}: {justificationsId}; Version 2", [
            "employeeId" => $this->employee_id,
            "date" => $this->date,
            "justificationsId" => $isDateJustified
        ]);

        // * get the records of employee at the target day
        $records = $this->getEmployeeRecords($this->date);
        Log::debug("Record of the employee: {records}", [
            "records" => $records
        ]);

        if(empty($records))
        {
            if (!$isDateJustified)
            {
                $this->storeFalta();
                Log::debug("Records are empty, Store the incident 'falta' (Condition A)");
            }
            elseif ( empty(array_intersect(self::JUSTIFICA_DIAS_IDS, $isDateJustified)) )
            {
                $this->storeFalta();
                Log::debug("Records are empty, Store the incident 'falta' (Condition B)");
            }
            else
            {
                Log::debug("Records are empty, Not store the incident 'falta', the day is justifed");
            }
            Log::debug("End calculate incidents for employee {employeeId}");
            return True;
        }

        // * get the matching records of the employee based of the working hours
        $matchingRecords = $this->matchingCheckins($records, $this->working_hours, $this->date);
        Log::debug("Matching records for employee {employeeId}: {matchingRecords", [
            "employeeId" => $this->employee_id,
            "matchingRecords" => $matchingRecords,
        ]);

        // * get the assessment of the incidents
        $assessment = $this->assessIncidentsV2($this->working_hours, $matchingRecords);
        Log::debug("Incidents assessment for employee {employeeId}: {assessment}", [
            "employeeId" => $this->employee_id,
            "assessment" => $assessment,
        ]);

        foreach ($assessment as $row)
        {
            if ($row['incident'] != 'A Tiempo')
            {
                if (!$isDateJustified || !$this->isIncidentJustified($row, $isDateJustified))
                {
                    $this->{$this->getIncidentMethod($row)}();
                }
            }
        }

        Log::debug("End calculate incidents for employee {employeeId}");
        return True;
    }

    /**
     * Checks if the date is justified for an employee.
     *
     * @return array list of justifies types id's if the date is justified, false otherwise.
     */
    public function isDateJustified()
    {
        $date = new Carbon($this->date);
        $justifiesRecords = false;

        $justifiesOfTheDay = Justify::where('employee_id', $this->employee_id)
            ->where(function ($query) use ($date) {
                $query->where('date_start', '<=', $date->format('Y-m-d'))
                    ->orWhere(function ($subQuery) use ($date) {
                        $subQuery->where('date_finish', '>=', $date->format('Y-m-d'))
                            ->orWhereNull('date_finish');
                    });
            })
            ->get();

        Log::debug("Justification for employee '{employeeID}', of the date '{date}'", [
            'employeeID' => $this->employee_id,
            'date' => $date->format('Y-m-d'),
            'justifications' => $justifiesOfTheDay
        ]);

        foreach ($justifiesOfTheDay as $justify)
        {
            $start = new Carbon($justify->date_start);
            $end = isset($justify->date_finish) ? new Carbon($justify->date_finish) : $start;

            Log::debug("Jutifie from {start} to {end} > {targetDate}", [
                'start' => $start,
                'end' => $end,
                'targetDate' => $date->format('Y-m-d')
            ]);

            if ($date->between($start, $end)) {
                $justifiesRecords[] = $justify->type_justify_id;
            }
        }

        return $justifiesRecords;
    }

    public function removeDuplicateRecords() 
    {
        $records = Record::where('employee_id', $this->employee_id)
            ->whereDate('check', $this->date)
            ->get()
            ->sortBy('check'); // Ensure records are sorted for comparison

        // Filter records to remove those within one minute of each other
        $filteredRecords = $records->reduce(function ($carry, $item) {
            if (!$carry->isEmpty()) {
                $lastItem = $carry->last();
                $lastTime = new Carbon($lastItem->check, 'UTC');
                $currentTime = new Carbon($item->check, 'UTC');

                // Only add current item if more than one minute difference from the last item
                if ($currentTime->diffInSeconds($lastTime) > 60) {
                    $carry->push($item);
                }
            } else {
                // First item is always added
                $carry->push($item);
            }
            return $carry;
        }, collect());

        return $filteredRecords;
    }

    /**
     * removeDuplicateRecordsV2
     *
     * @return Collection<Record>
     */
    public function removeDuplicateRecordsV2()
    {
        /** @var Record[] $records */
        $records = Record::where('employee_id', $this->employee_id)
            ->whereDate('check', $this->date)
            ->get()
            ->sortBy('check')
            ->all(); // Ensure records are sorted for comparison

        // Filter records to remove those within one minute of each other
        /** @var Record[] $filteredRecords */
        $filteredRecords = array();

        foreach($records as $record){
            $duplicated = false;
            $currentDate = new DateTime($record->check);
            foreach($filteredRecords as $storedRecord){
                $diff = $currentDate->diff(new DateTime($storedRecord->check));
                // Convert the difference to total minutes
                $minutesDifference = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                $duplicated = $minutesDifference <= 5;
            }

            if($duplicated){
                continue;
            }

            array_push($filteredRecords, $record);
        }

        return collect($filteredRecords);
    }

    private function isMoreAppropriateRecord($newDiff, $currentDiff) 
    {
        // Logic to determine if a new record is more suitable for the type
        return abs($newDiff) < abs($currentDiff);
    }
    
    private function determineIncident($type, $diffInMinutes) 
    {
        switch ($type) {
            case 'checkin':
                if ($diffInMinutes > 20) return 'Retardo Mayor';
                if ($diffInMinutes > 10) return 'Retardo Menor';
                if ($diffInMinutes <= 10) return 'A Tiempo';
                break;
            case 'toeat':
                if ($diffInMinutes < 0) return 'Salio Antes';
                if ($diffInMinutes >= 0) return 'A Tiempo';
            case 'toarrive':
                if ($diffInMinutes > 10) return 'Retardo Mayor';
                if ($diffInMinutes <= 10) return 'A Tiempo';
                break;
            case 'checkout':
                if ($diffInMinutes < 0) return 'Salio Antes';
                if ($diffInMinutes >= 0) return 'A Tiempo';
                break;
        }
        return 'Falta';  // Default if no condition is met
    }

    private function getIncidentMethod($row)
    {
        return self::INCIDENT_METHOD_MAP[$row['type']][$row['incident']] ?? self::INCIDENT_METHOD_MAP[$row['type']]['Falta'];
    }

    private function isIncidentJustified($row, $isDateJustified)
    {
        $incidentType = $row['incident'];
        $incidentJustificationMap = [
            'Retardo Menor' => [self::JUSTIFICA_RETARDO_MENOR_ID],
            'Retardo Mayor' => [self::JUSTIFICA_RETARDO_MAYOR_ID],
            'Falta' => self::JUSTIFICA_DIAS_IDS, // Assuming Falta can be justified by any day-justification
            'Salio Antes' => self::JUSTIFICA_OMISION_SALIDA_IDS,
            'default' => [],
        ];

        // Handle specific cases for check-in and check-out omisions
        if ($row['type'] == 'checkin' && ($incidentType == 'Omision Entrada' || $incidentType == 'Falta')) {
            $justificationIds = self::JUSTIFICA_OMISION_ENTRADA_IDS;
        } elseif ($row['type'] == 'checkout' && ($incidentType == 'Omision Salida' || $incidentType == 'Falta')) {
            $justificationIds = self::JUSTIFICA_OMISION_SALIDA_IDS;
        } else {
            $justificationIds = $incidentJustificationMap[$incidentType] ?? $incidentJustificationMap['Falta'];
        }

        // Check if any of the justifications for the incident type are in the provided justifications
        foreach ($justificationIds as $id) {
            if (in_array($id, $isDateJustified)) {
                return true;
            }
        }

        return false;
    }

    /**
     * get an array of records
     * @param DateTime $date
     * @return array<Record>
     */
    private function getEmployeeRecords(DateTime $date)
    {
        /** @var Record[] $records */
        $records = Record::where('employee_id', $this->employee_id)
            ->select('id', 'employee_id', 'check')
            ->whereDate('check', $date)
            ->get()
            ->sortBy('check')
            ->all(); // Ensure records are sorted for comparison

        return $records;
    }

    /**
     * matchingCheckins
     *
     * @param  array<Record> $records
     * @param  WorkingHours $workingHours
     * @return array
     */
    private function matchingCheckins(array $records, WorkingHours $workingHours, DateTime $targetDate)
    {
        // * define witch type of workinghours has the employee and prepare the array for store the matching date 'check'
        $workingHoursArray = array();
        if ($workingHours->toeat && $workingHours->toarrive)
        {
            // combine the targetDate with the working hours when the working hours format is like H:i
            array_push($workingHoursArray,
                [ (clone $targetDate)->setTime(
                    hour: explode(':', $workingHours->checkin)[0],
                    minute: explode(':', $workingHours->checkin)[1]
                    ),
                    null
                ],
                [ (clone $targetDate)->setTime(
                    hour: explode(':', $workingHours->toeat)[0],
                    minute: explode(':', $workingHours->toeat)[1]
                    ),
                    null
                ],
                [ (clone $targetDate)->setTime(
                    hour: explode(':', $workingHours->toarrive)[0],
                    minute: explode(':', $workingHours->toarrive)[1]
                    ),
                    null
                ],
                [ (clone $targetDate)->setTime(
                    hour: explode(':', $workingHours->checkout)[0],
                    minute: explode(':', $workingHours->checkout)[1]
                    ),
                    null
                ]
            );
        } else
        {
            array_push($workingHoursArray,
                [ (clone $targetDate)->setTime(
                    hour: explode(':', $workingHours->checkin)[0],
                    minute: explode(':', $workingHours->checkin)[1]
                    ),
                    null
                ],
                [ (clone $targetDate)->setTime(
                    hour: explode(':', $workingHours->checkout)[0],
                    minute: explode(':', $workingHours->checkout)[1]
                    ),
                    null
                ]
            );
        }

        $continue = true;
        $loop = 1;
        $minutesRange = 30;
        $tmpRecords = array_map(fn($record) => clone $record, $records); // * Clone the array of records
        while ($continue)
        {
            Log::debug("TempRecords: {tmpRecords} on loop {loop} with range +-{minutes}", [
                "loop" => $loop,
                "minutes" => $minutesRange,
                "tmpRecords" => $tmpRecords,
                "workingHoursArray" => $workingHoursArray
            ]);

            for ($i=0; $i<count($workingHoursArray); $i++)
            {
                // * skip if the current 'hour to check' has a match stored already
                if($workingHoursArray[$i][1] != null)
                {
                    continue;
                }

                /** @var DateTime $currentCheckDate */
                $currentCheckDate = $workingHoursArray[$i][0];
                $currentCheckDateFrom = (clone $currentCheckDate)->sub(new DateInterval('PT' . $minutesRange . 'M'));
                $currentCheckDateTo = (clone $currentCheckDate)->add(new DateInterval('PT' . $minutesRange . 'M'));

                // * Get an array of checks if they are in the range of `$currentCheckDateFrom` and `$currentCheckDateTo`
                $targetChecks = array_values(
                    array_filter($tmpRecords, function($record) use ($currentCheckDateFrom, $currentCheckDateTo) {
                        $checkTime = new DateTime($record->check);
                        return $checkTime >= $currentCheckDateFrom && $checkTime <= $currentCheckDateTo;
                    })
                );

                if(empty($targetChecks))
                {
                    continue;
                }


                // * get the type of the datecheck ['checkin', 'checkout'] based on if is odd or even
                $isChekin = (($i + 1) % 2) != 0;
                $workingHoursArray[$i][1] = $isChekin
                    ? $targetChecks[0]
                    : end($targetChecks);

                // * remove the matched date from the records
                $tmpRecords = array_diff($tmpRecords, $targetChecks);

            }

            // * check if the recods still has elements
            if(empty($tmpRecords))
            {
                $continue = false;
            }

            // * check if all the working hours has a match
            if(count(array_filter($workingHoursArray, fn ($elm) => $elm[1] == null)) == 0)
            {
                $continue = false;
            }

            // * check if the loop has reached the limit
            if($loop == 16)
            {
                $continue = false;
            }

            $loop ++;
            $minutesRange += 15;
        }

        return $workingHoursArray;
    }
}
