<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HollidaysRequest extends FormRequest
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
            "initialDay" => "required|date",
            "endDay" => "nullable|date|after:initialDay",
            "type_id" => "required|integer|exists:type_justifies,id",
            "file" => "required|file|mimes:pdf|max:15240",
            "general_direction" => 'required|integer|exists:general_directions,id',
            "employees" => 'required|array|min:1',
            "employees.*" => 'required|integer',
            "comments" => 'nullable|string|max:200'
        ];
    }

    public function attributes()
    {
        return [
            "initialDay" => "fecha inicial",
            "endDay" => "fecha final",
            "type_id" => "tipo de justificacion",
            "general_direction" =>"direccion general",
            "file" => "oficio",
            "employees" => "empleados",
            "comments" => "comentarios",
        ];
    }

}
