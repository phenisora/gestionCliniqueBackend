<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function creerOrdonnance(Request $request){
        $data = $request->validate([

          'appointment_id' => 'required',
            'diagnosis'      => 'required',
            'medications'    => 'required',
            'notes'          => 'nullable'
        ]);
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
