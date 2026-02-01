<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrescriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Seul un docteur peut créer une ordonnance
        return auth('api')->user()->role === 'doctor';
    }

    public function rules(): array
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
            'diagnosis'      => 'required|string|min:5',
            'medications'    => 'required|string',
            'notes'          => 'nullable|string'
        ];
    }
}
