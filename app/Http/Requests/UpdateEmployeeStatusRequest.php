<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeStatusRequest extends FormRequest
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
            "comments" => "nullable|string|max:250",
            "status_id" => "nullable|numeric",
            "file" => "nullable|mimes:pdf|max:15360", // 15,360 KB = 15 MB
        ];
    }

    public function attributes() : array
    {
        return [
            "comments" => "comentarios",
            "file" => "archivo de justificacion",
            "status_id" => "estatus",
        ];
    }

}
