<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('userid');
        
        return [
            "generalDirection_id" => 'required|integer|min:1',
            "name" => 'required|string|max:120',
            "email" => 'required|email|unique:users,email,' . $userId,
            "level_id" => 'required|integer|min:0|max:5',
            "options" => 'required|array|min:1'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            "generalDirection_id" => 'fiscalia, direccion general',
            "direction_id" => 'direccion, vicefiscalia',
            "subdirectorate_id" => 'subdireccion, agencia',
            "departments_id" => 'departamento',
            "name" => 'nombre',
            "email" => 'correo',
            "level_id" => 'nivel de acceso',
            "options" => 'acceso menu'
        ];
    }
}
