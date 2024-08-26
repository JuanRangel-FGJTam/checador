<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NewJustificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //TODO: validate if the user has the rigth level to store a justification
        // Auth::user()->level_id;
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
            "multipleDays" => "required|boolean",
            "initialDay" => "required|date",
            "endDay" => "nullable|required_if:multipleDays,true|date|after:date:initialDay",
            "type_id" => "required|integer|exists:type_justifies,id",
            "comments" => "nullable|string|max:250",
            "file" => "required|mimes:pdf|max:15360", // 15,360 KB = 15 MB
        ];
    }

    public function attributes(): array
    {
        return [
            "multipleDays" => "multiples dias",
            "initialDay" => "fecha a justificar",
            "endDay" => "fecha final",
            "type_id" => "tipo de justificacion",
            "comments" => "comentarios",
            "file" => "archivo de justificacion"
        ];
    }

}
