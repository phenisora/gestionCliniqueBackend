<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    // GET /api/appointments?doctor_id=1&date=2025-01-20&status=scheduled
public function index(Request $request)
{
    $query = Appointment::with(['patient.user', 'doctor.user']);
    
    if ($request->user()->role === 'patient') {
        $query->where('patient_id', $request->user()->patient->id);
    } elseif ($request->user()->role === 'doctor') {
        $query->where('doctor_id', $request->user()->doctor->id);
    }
    
    if ($request->doctor_id) {
        $query->where('doctor_id', $request->doctor_id);
    }
    if ($request->date) {
        $query->whereDate('date', $request->date);
    }
    if ($request->status) {
        $query->where('status', $request->status);
    }
    
    return $query->orderBy('date')->orderBy('time')->paginate(15);
}


}
