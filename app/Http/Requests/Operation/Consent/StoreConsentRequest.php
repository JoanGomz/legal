<?php

namespace App\Http\Requests\Operation\Consent;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsentRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        // Cambiar a true para permitir que cualquier usuario use el formulario
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'park_id' => 'required|integer',
            'arcade_id' => 'nullable|integer',
            'document_number' => 'required|string|min:5|max:13',
            'document_type' => 'nullable|string|in:CC,CE,PS', // Cédula, Extranjería, Pasaporte
            'full_name' => 'required|string|min:3|max:255',
            'relationship' => 'nullable|string|max:50',
            'phone' => 'required|string|min:7|max:15',
            'email' => 'nullable|email',

            // Datos del Menor
            'minor_document_number' => 'required|string|min:5|max:20',
            'minor_document_type' => 'nullable|string|in:RC,TI', // Registro Civil, Tarjeta Identidad
            'minor_full_name' => 'required|string|min:3|max:255',
            'minor_birth_date' => 'nullable|date|before:today',

            // Validaciones de consentimiento (deben ser aceptadas)
            'check_uno' => 'nullable|accepted',
            'check_dos' => 'nullable|accepted',
            'check_tres' => 'nullable|accepted',
        ];
    }

    /**
     * Mensajes personalizados (Opcional).
     */
    public function messages(): array
    {
        return [
            'accepted' => 'Debes aceptar todos los términos de consentimiento para continuar.',
            'minor_birth_date.before' => 'La fecha de nacimiento no puede ser futura.',
        ];
    }
}
