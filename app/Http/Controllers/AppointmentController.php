<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Availability;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    
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

    // Déterminer le patient_id
    $patientId = ($user->role === 'patient') ? $user->patient->id : $request->patient_id;

    //  Logique Métier : Vérifier si le docteur est déjà pris à cette heure
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

public function show($id) {
    $user = auth('api')->user();
    
    // On cherche le rendez-vous spécifique
    $appointment = Appointment::with(['doctor.userD', 'patient.user'])->findOrFail($id);

    // Sécurité : On vérifie que l'utilisateur a le droit de le voir
    if ($user->role === 'patient' && $appointment->patient_id !== $user->patient->id) {
        return response()->json(['message' => 'Accès refusé'], 403);
    }
    
    if ($user->role === 'doctor' && $appointment->doctor_id !== $user->doctor->id) {
        return response()->json(['message' => 'Accès refusé'], 403);
    }

    return response()->json($appointment);
}

public function updateStatus(Request $request, $id) {
    $request->validate([
        'status' => 'required|in:scheduled,confirmed,in_progress,completed,cancelled,no_show'
    ]);

    $appointment = Appointment::findOrFail($id);
    $appointment->update(['status' => $request->status]);

    return response()->json(['message' => 'Statut mis à jour avec succès']);
}
public function update(Request $request, $id) {
    $appointment = Appointment::findOrFail($id);
    
    // Validation des données modifiables
    $validated = $request->validate([
        'date' => 'sometimes|required|date|after_or_equal:today',
        'time' => 'sometimes|required',
        'doctor_id' => 'sometimes|required|exists:doctors,id',
        'reason' => 'sometimes|required|string',
        'notes' => 'nullable|string'
    ]);

    // Logique métier : Si on change la date ou l'heure, on vérifie la disponibilité
    if ($request->has('date') || $request->has('time')) {
        $date = $request->date ?? $appointment->date;
        $time = $request->time ?? $appointment->time;
        $doctorId = $request->doctor_id ?? $appointment->doctor_id;

        $exists = Appointment::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('time', $time)
            ->where('id', '!=', $id) // On ignore le rendez-vous actuel
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ce créneau est déjà pris pour ce docteur.'], 422);
        }
    }

    $appointment->update($validated);

    return response()->json([
        'message' => 'Rendez-vous mis à jour avec succès',
        'data' => $appointment
    ]);
}

public function destroy($id) {
    $appointment = Appointment::findOrFail($id);
    $user = auth('api')->user();

    // Sécurité : Un patient ne peut annuler que son propre RDV
    if ($user->role === 'patient' && $appointment->patient_id !== $user->patient->id) {
        return response()->json(['message' => 'Action non autorisée'], 403);
    }

    $appointment->update(['status' => 'cancelled']);
    return response()->json(['message' => 'Rendez-vous annulé']);
}

public function availableSlots(Request $request) {
    
    $request->validate([
        'doctor_id' => 'required|exists:doctors,id',
        'date' => 'required|date|after_or_equal:today'
    ]);

    // Convertir la date en jour de la semaine
    $dayOfWeek = strtolower(date('l', strtotime($request->date)));

    // Chercher si le docteur a configuré ses heures pour ce jour précis
    $availability = Availability::where('doctor_id', $request->doctor_id)
        ->where('day_of_week', $dayOfWeek)
        ->where('is_available', true) // On vérifie qu'il n'est pas en congé
        ->first();

    if (!$availability) {
        return response()->json(['message' => 'Aucune disponibilité pour ce jour.'], 200);
    }

    // Générer les créneaux 
    $slots = [];
    $start = strtotime($availability->start_time);
    $end = strtotime($availability->end_time);

    // On avance de 30 minutes en 30 minutes 
    while ($start < $end) {
        $slots[] = date('H:i', $start);
        $start = strtotime('+30 minutes', $start);
    }

    // Filtrer les créneaux déjà réservés dans la table appointments
    $booked = Appointment::where('doctor_id', $request->doctor_id)
        ->where('date', $request->date)
        ->whereNotIn('status', ['cancelled']) // On ignore les annulés
        ->pluck('time')
        ->map(fn($t) => substr($t, 0, 5))
        ->toArray();

    $freeSlots = array_values(array_diff($slots, $booked));

    return response()->json($freeSlots);
}

public function byDate($date) {
    $user = auth('api')->user();

    $query = Appointment::with(['doctor.userD', 'patient.user'])
        ->whereDate('date', $date);

    // Si c'est un docteur, on filtre pour qu'il ne voie que ses RDV de cette date
    if ($user->role === 'doctor') {
        $query->where('doctor_id', $user->doctor->id);
    }

    $appointments = $query->get();

    return response()->json($appointments);
}

public function doctorAppointments($doctor_id) {
    $user = auth('api')->user();

    // Sécurité : Un docteur ne peut voir que SES rendez-vous
    if ($user->role === 'doctor' && $user->doctor->id != $doctor_id) {
        return response()->json(['message' => 'Accès non autorisé à cet agenda'], 403);
    }

    $appointments = Appointment::with(['patient.user'])
        ->where('doctor_id', $doctor_id)
        ->orderBy('date', 'asc')
        ->orderBy('time', 'asc')
        ->get();

    return response()->json($appointments);
}

}
