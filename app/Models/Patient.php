<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'blood_type',
        'address',
        'emergency_contact'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
