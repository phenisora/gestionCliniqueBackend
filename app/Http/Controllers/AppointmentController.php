<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    // GET /api/appointments?doctor_id=1&date=2025-01-20&status=scheduled
public function index(Request $request)
{
    $query = Appointment::with(['patient.user', 'doctor.userD']);
    
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

public function store(Request $request) {
    $user = auth('api')->user();
    
    $validated = $request->validate([
        'doctor_id' => 'required|exists:doctors,id',
        'date' => 'required|date|after_or_equal:today',
        'time' => 'required',
        'reason' => 'required|string',
        'patient_id' => $user->role === 'receptionist' ? 'required|exists:patients,id' : 'nullable'
    ]);

    // 1. Déterminer le patient_id
    $patientId = ($user->role === 'patient') ? $user->patient->id : $request->patient_id;

    // 2. Logique Métier : Vérifier si le docteur est déjà pris à cette heure
    $exists = Appointment::where('doctor_id', $request->doctor_id)
        ->where('date', $request->date)
        ->where('time', $request->time)
        ->where('status', '!=', 'cancelled')
        ->exists();

    if ($exists) {
        return response()->json(['message' => 'Ce créneau est déjà réservé.'], 422);
    }

    $appointment = Appointment::create(array_merge($validated, ['patient_id' => $patientId]));
    return response()->json($appointment, 201);
}



}
