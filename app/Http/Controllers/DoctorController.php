<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    // Fonction qui affiche la liste des médecins
    public function index(){
        $doctors = Doctor::all();
       return response()->json([

       'status'=>'success',
       'data'=> $doctors
       ],201) ;
    }
     // Fonction qui affiche les détails d'un médecin
     public function detail(Doctor $doctor){

     return response()->json([
        'status' => 'success',
        //'data' => $doctors
    ]);


     }

     public function store(Request $request) {
        $doctors = Doctor::create($request->validate([
            'user_id' => 'required',
            'specialty_id' => 'required',
            'license_number' => 'required|unique:doctors',
            'consultation_fee' => 'required|numeric'
        ]));
        return response()->json($doctors, 201);
    }

    // Modif d'un médecin
    public function update(Request $request, Doctor $doctors) {
        $doctors->update($request->all());
        return response()->json($doctors);
    }

    // Supp un médecin
    public function supprimer(Doctor $doctors) {
        $doctors->delete();
        return response()->json(['message' => 'Médecin supprimé avec succès']);
    }


    // Médecins par spécialité (Endpoint 7)
    public function doctorsParpecialty($id) {
        return response()->json(Doctor::where('specialty_id', $id)->get());
    }

}
