<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable=[

    'appointment_id',
    'diagnosis',
    'medications',
    'notes	'

    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
