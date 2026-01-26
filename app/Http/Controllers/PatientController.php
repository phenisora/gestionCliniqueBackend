<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Liste des patients (Médecin/Réceptionniste)
     */
    public function index(Request $request)
    {
        $query = Patient::with(['user']);

        // Recherche par nom
        if ($request->has('search')) {
            $query->
            whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filtre par groupe sanguin
        if ($request->has('blood_type')) {
            $query->where('blood_type', $request->blood_type);
        }

        $patients = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => PatientResource::collection($patients),
            'pagination' => [
                'total' => $patients->total(),
                'per_page' => $patients->perPage(),
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
            ],
        ]);
    }

    /**
     * Détails d'un patient
     */
    public function show($id)
    {

        /* `=Patient::with(['user'])->findOrFail(); est en train de récupérer un enregistrement
        de patient ainsi que son enregistrement utilisateur associé
        en utilisant le chargement anticipé. Cela signifie que lorsque
        l'enregistrement du patient est récupéré, l'enregistrement utilisateur associé est
        également chargé dans la même requête pour éviter des requêtes supplémentaires à
        la base de données pour chaque information utilisateur du patient.
        La méthode findOrFail() est utilisée pour trouver un patient par sa clé primaire (id)
          et si le patient n'est pas trouvé, elle lancera une ModelNotFoundException. */
                $patient = Patient::with(['user'])->findOrFail($id);

        // Vérifier l'autorisation
        $user = auth('api')->user();
        if ($user->Patient() && $user->patient->id !== $patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new PatientResource($patient),
        ]);
    }

    /**
     * Mettre à jour un patient
     */
   public function update(Request $request, $id)
{
    $patient = Patient::findOrFail($id);

    $user = auth('api')->user();

    // 🔐 Autorisation
    if (
        $user->patient &&                 // l'utilisateur est un patient
        $user->patient->id !== $patient->id && // pas son propre profil
        $user->role !== 'receptionist'    // pas réceptionniste
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Non autorisé',
        ], 403);
    }

    $validated = $request->validate([
        'date_of_birth' => 'sometimes|date',
        'gender' => 'sometimes|in:male,female,other',
        'blood_type' => 'sometimes|string',
        'address' => 'sometimes|string',
        'emergency_contact' => 'sometimes|string',
        'allergies' => 'sometimes|string',
    ]);

    $patient->update($validated);

    // Mise à jour des infos utilisateur
    if ($request->hasAny(['name', 'phone'])) {
        $patient->user->update(
            $request->only(['name', 'phone'])
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'Patient mis à jour avec succès',
        'data' => new PatientResource($patient->fresh('user')),
    ], 200);
}


    /**
     * Supprimer un patient (Réceptionniste uniquement)
     */
    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->user->delete(); // Cascade sur patient

        return response()->json([
            'success' => true,
            'message' => 'Patient supprimé avec succès',
        ]);
    }

    /**
     * Historique médical d'un patient
     */
    public function medicalHistory($id)
    {
        $patient = Patient::with([
            'appointments' => function ($query) {
                $query->where('status', 'completed')
                    ->with(['doctor.user', 'doctor.specialty', 'prescription'])
                    ->orderByDesc('date');
            },
            'medicalRecords' => function ($query) {
                $query->with('doctor.user')->orderByDesc('created_at');
            }
        ])->findOrFail($id);

        // Vérifier l'autorisation
        $user = auth()->user();
        if ($user->Patient() && $user->patient->id !== $patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'patient' => [
                    'id' => $patient->id,
                    'name' => $patient->user->name,
                    'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
                    'blood_type' => $patient->blood_type,
                    'allergies' => $patient->allergies,
                ],
                'appointments' => $patient->appointments->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'date' => $appointment->date->format('Y-m-d'),
                        'time' => $appointment->time,
                        'doctor' => $appointment->doctor->user->name,
                        'specialty' => $appointment->doctor->specialty->name,
                        'reason' => $appointment->reason,
                        'notes' => $appointment->notes,
                        'prescription' => $appointment->prescription ? [
                            'diagnosis' => $appointment->prescription->diagnosis,
                            'medications' => $appointment->prescription->medications,
                            'notes' => $appointment->prescription->notes,
                        ] : null,
                    ];
                }),
                'medical_records' => $patient->medicalRecords->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->created_at->format('Y-m-d'),
                        'type' => $record->type,
                        'doctor' => $record->doctor->user->name,
                        'description' => $record->description,
                        'attachments' => $record->attachments,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Rendez-vous d'un patient
     */
    public function appointments($id)
    {
        $patient = Patient::findOrFail($id);

        // Vérifier l'autorisation
        $user = auth('api')->user();
        if ($user->Patient() && $user->patient->id !== $patient->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        $appointments = $patient->appointments()
            ->with(['doctor.user', 'doctor.specialty'])
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }
}
