<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class NewUserRequest extends FormRequest
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
            "generalDirection_id" => 'required|integer|min:1',
            "direction_id" => 'nullable|integer|min:1',
            "subdirectorate_id" => 'nullable|integer|min:1',
            "departments_id" => 'nullable|integer|min:1',
            "name" => 'required|string|max:120',
            "email" => 'required|email|unique:users,email',
            "password" => 'required|string|min:8|max:24',
            "password_confirmation" => 'required|string|min:8|max:24|same:password',
            "level_id" => 'nullable|integer|min:0|max:5'
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
            "password" => 'contraseña',
            "password_confirmation" => 'confirmar contraseña'
        ];
    }

}
