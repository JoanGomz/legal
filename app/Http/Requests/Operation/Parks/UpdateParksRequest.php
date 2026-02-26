<?php

namespace App\Http\Requests\Operation\Parks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateParksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string'],
            'horarios' => ['required', 'string'],
            'direccion' => ['required', 'string'],
            'id_ciudad' => ['required', 'integer'],
            'compania_id' => ['required', 'integer']
        ];
    }
}
