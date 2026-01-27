<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->date_of_birth?->age,
            'gender' => $this->gender,
            'blood_type' => $this->blood_type,
            'address' => $this->address,
            'emergency_contact' => $this->emergency_contact,
            'allergies' => $this->allergies,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

}


