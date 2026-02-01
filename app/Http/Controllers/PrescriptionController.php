<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Http\Request;
use App\Http\Requests\PrescriptionRequest;

class PrescriptionController extends Controller
{
    public function creerOrdonnance(PrescriptionRequest $request) 
{
    // On récupère les données validées
    $data = $request->validated();

    //Sécurité : Vérifier que le rendez-vous appartient bien au docteur connecté
    $appointment = Appointment::findOrFail($data['appointment_id']);
    $user = auth('api')->user();

    if ($appointment->doctor_id !== $user->doctor->id) {
        return response()->json([
            'message' => 'Action non autorisée. Ce rendez-vous ne vous est pas assigné.'
        ], 403);
    }

    //  Ajout de l'ID du docteur à l'ordonnance (si ta table prescriptions a une colonne doctor_id)
    $data['doctor_id'] = $user->doctor->id;

    // Création
    $prescription = Prescription::create($data);

    return response()->json([
        'message' => 'Ordonnance créée avec succès',
        'data' => $prescription
    ], 201);
}

    public function modifierOrdonnance(Request $request, $id){
        $prescription = Prescription::findOrFail($id);

        // On met à jour
        $prescription->update($request->all());

        return response()->json([
            'message' => 'Ordonnance modifiée avec succés',
            'data' => $prescription
        ]);

    }

    public function supprimerOrdonnance($id){
        $prescription = Prescription::findOrFail($id);
        $prescription->delete();

        return response()->json([
            'message' => 'Ordonnance supprimée avec succès'
        ]);

    }

    public function mesOrdonnance(){
        $user = auth('api')->user();
        $prescriptions = Prescription::whereHas('appointment.patient', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('appointment.doctor.user')->get();

        return response()->json([
            'data' => $prescriptions
        ]);
    }
    public function detailsOrdonnance($id){
        $user = auth('api')->user();
        $prescription = Prescription::where('id', $id)
            ->whereHas('appointment.patient', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('appointment.doctor.user')
            ->firstOrFail();

        return response()->json([
            'data' => $prescription
        ]);
    }

    public function ordonnancesRDV($id){
        $prescriptions = Prescription::where('appointment_id', $id)->get();

        return response()->json([
            'data' => $prescriptions
        ]);
    }
}
