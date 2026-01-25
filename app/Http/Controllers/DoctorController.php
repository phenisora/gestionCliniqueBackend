<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Availability;
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

    // Médecins par spécialité 
    public function doctorsParpecialty($id) {
        return response()->json(Doctor::where('specialty_id', $id)->get());
    }


    // Méthode pour récupérer les disponibilités d'un médecin
   public function dispoDunmedecin($id)
{
    // 1. On récupère le médecin
    $doctors = Doctor::find($id);

    // 2. Si le médecin n'existe pas, message d'erreur
    if (!$doctors) {
        return response()->json(['message' => 'Médecin non trouvé'], 404);
    }
    // 3. On retourne JUSTE les disponibilités 
    return response()->json([
        'medecin_id' => $doctors->id,
        'disponibilites' => $doctors->availabilities
    ], 200);
}


public function medecinsParSpecialite($id)
{
    // 1. Récupérer tous les docteurs ayant cette spécialité
    // On charge 'user' pour avoir leurs noms
    $doctors = Doctor::with('userD')
        ->where('specialty_id', $id)
        ->get();

    // 2. Vérifier si la liste est vide
    if ($doctors->isEmpty()) {
        return response()->json(['message' => 'Aucun médecin trouvé pour cette spécialité'], 404);
    }

    // 3. Retourner la liste
    return response()->json([
        'specialty_id' => $id,
        'nombre' => $doctors->count(),
        'medecins' => $doctors
    ], 200);
}

 



public function definirAvailabilities(Request $request, $id)
{
    //  On trouve le médecin
    $doctor = Doctor::findOrFail($id);
    //  On supprime les anciens créneaux pour éviter les doublons
    $doctor->availabilities()->delete();
    // On insère tout le tableau d'un coup
    $doctor->availabilities()->createMany($request->availabilities);
    return response()->json(['message' => 'Disponibilités enregistrées !'], 201);
}

public function searchParNom(Request $request)
    {
        $nom = $request->name;
        // On cherche dans la table 'users' liée
        return Doctor::whereHas('user', function($q) use ($nom) {
            $q->where('name', 'like', "%$nom%");
        })->get();

    }


// 3. Médecins disponibles à une date
    public function availableParDate(Request $request)
    {
        // On transforme la date (ex: 2026-01-20) en jour (ex: tuesday)
        $jour = strtolower(date('l', strtotime($request->date)));
        return Doctor::whereHas('availabilities', function($q) use ($jour) {
            $q->where('day_of_week', $jour)->where('is_available', 1);
        })->get();
    }


}
