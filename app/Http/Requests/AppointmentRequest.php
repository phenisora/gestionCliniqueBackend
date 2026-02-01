<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
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
        $user = auth('api')->user();
        return [
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:500',
            'patient_id' => $user->role === 'receptionist' ? 'required|exists:patients,id' : 'nullable'

        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->isSlotAvailable()) {
                $validator->errors()->add('time', 'Ce créneau n\'est pas disponible');
            }
        });
    }

    protected function isSlotAvailable()
    {
        return !Appointment::where('doctor_id', $this->doctor_id)
            ->where('date', $this->date)
            ->where('time', $this->time)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }
}
