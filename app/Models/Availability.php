<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Availability extends Model
{
    // 1. Définir les colonnes que Laravel a le droit de remplir
    use HasFactory;
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    // 2. Relation inverse : Une disponibilité appartient à un docteur
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }


    public function availabilities()
{
    return $this->hasMany(Availability::class);
}


}
