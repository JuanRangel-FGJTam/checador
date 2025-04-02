<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => "required|string|max:100",
            "general_direction_id" => "required|numeric|exists:general_directions,id",
            "direction_id" => "nullable|numeric|exists:directions,id",
            "subdirectorate_id" => "nullable|numeric|exists:subdirectorates,id",
            "department_id" => "nullable|numeric|exists:departments,id",
            "scheduleType" => "required|numeric|min:1|max:2",
            "checkin" => "required|date_format:H:i",
            "toeat" => "required|date_format:H:i|after:checkin",
            "toarrive" => "nullable|required_if:scheduleType,2|date_format:H:i|after:toeat",
            "checkout" => "nullable|required_if:scheduleType,2|date_format:H:i|after:toarrive",
            "midweek" => "nullable|boolean",
            "weekend" => "nullable|boolean",
            "midweek_or_weekend" => function($attribute, $value, $fail) {
                if (!$this->midweek && !$this->weekend) {
                    $fail("Debe seleccionarse al menos uno de 'entre semana' o de 'fin de semana'.");
                }
            },
        ];
    }

    public function attributes() : array
    {
        return [
            "name" => "nombre del empleado",
            "general_direction_id" => "direccion general",
            "direction_id" => "direccion",
            "subdirectorate_id" => "sub direccion",
            "department_id" => "departamento",
            "scheduleType" => "tipo de horario",
            "checkin" => "hora de entrada",
            "toeat" => "hora de salida",
            "toarrive" => "hora de regreso de comer",
            "checkout" => "hora de salida",
            "midweek" => "entre semana ",
            "weekend" => "fin de semana"
        ];
    }
}
