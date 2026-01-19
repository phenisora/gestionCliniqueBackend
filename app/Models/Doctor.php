<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = ['user_id', 'specialty_id', 'license_number', 'consultation_fee', 'bio'];
}
