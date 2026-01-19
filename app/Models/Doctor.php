<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    

    protected $fillable =[
        'user_id',
        'name',
        'email',
        'password',
        'role',
        'specialty_id',
        'license_number',
        'consultation_fee',
        'bio',
        'phone'
    ];

    public function userD(){
        return $this->belongsTo(User::class);
    }

}
