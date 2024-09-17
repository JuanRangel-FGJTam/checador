<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\{Incident, Justify, Record};

class IncidentService
{
    public $date;
    public $employee_id;
    public $working_hours;

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

    public function __construct($employee_id, $working_hours, $date)
    {
        $this->date = $date;
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
        $incident->record_id = isset($record) ?$record->id :null;
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

        $recordMarkers = array_fill_keys($types, null);  // Initialize all markers as null

        foreach ($types as $type) {
            foreach ($records as $record) {
                $checkTime = new Carbon($record['check']);
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

    public function calculateAndStoreIncidents()
    {
        $isDateJustified = $this->isDateJustified();
        $records = $this->removeDuplicateRecords();
        $assessment = $this->assessIncidents($this->working_hours, $records);
        
        // Delete previous incidents if exists
        $this->deleteAllByDay();

        if ($records->isEmpty()) {
            if (!$isDateJustified) {
                $this->storeFalta();
            } elseif (empty(array_intersect(self::JUSTIFICA_DIAS_IDS, $isDateJustified))) {
                $this->storeFalta();
            }

            return True;
        }

        foreach ($assessment as $row) {
            if ($row['incident'] != 'A Tiempo') {
                if (!$isDateJustified || !$this->isIncidentJustified($row, $isDateJustified)) {
                    $this->{$this->getIncidentMethod($row)}();
                }
            }
        }

        return True;
    }

    /**
     * Checks if a specific date is justified for an employee.
     *
     * @param int $employeeId The ID of the employee.
     * @param Carbon $date The date to check.
     * @return array Justify if the date is justified, false otherwise.
     */
    public function isDateJustified() 
    {
        $date = new Carbon($this->date);
        $justifiesRecords = false;

        $justifies = Justify::where('employee_id', $this->employee_id)
            ->where(function ($query) use ($date) {
                $query->where('date_start', '<=', $date->format('Y-m-d'))
                    ->orWhere(function ($subQuery) use ($date) {
                        $subQuery->where('date_finish', '>=', $date->format('Y-m-d'))
                            ->orWhereNull('date_finish');
                    });
            })
            ->get();

        foreach ($justifies as $justify) {
            $start = new Carbon($justify->date_start);
            $end = isset($justify->date_end) ? new Carbon($justify->date_end) : $start;

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
}
