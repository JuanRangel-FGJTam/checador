<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignAreaRequest extends FormRequest
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
            "direction_id" => "required|numeric|exists:directions,id",
            "subdirectorate_id" => "required|numeric|exists:subdirectorates,id",
            "department_id" => "nullable|numeric|exists:departments,id"
        ];
    }

    public function attributes() : array
    {
        return [
            "name" => "nombre del empleado",
            "general_direction_id" => "direccion general",
            "direction_id" => "direccion",
            "subdirectorate_id" => "sub direccion",
            "department_id" => "departamento"
        ];
    }

}
