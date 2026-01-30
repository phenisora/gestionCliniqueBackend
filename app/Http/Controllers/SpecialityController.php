<?php

namespace App\Http\Controllers;

use App\Models\Speciality;
use Illuminate\Http\Request;

class SpecialityController extends Controller
{
    public function index()
    {
        // récupère toutes les spécialités de la base de données
        $specialities = Speciality::all();

        // On retourne la collection en JSON
        return response()->json([
            'status' => 'success',
            'data' => $specialities
        ], 200);
    }

    public function show($id)
{
    // On récupère la spécialité avec ses médecins et les infos "User" de ces médecins
    $speciality = Speciality::with(['doctors.userD'])->find($id);

    if (!$speciality) {
        return response()->json(['message' => 'Spécialité non trouvée'], 404);
    }

    return response()->json([
        'status' => 'success',
        'speciality_name' => $speciality->name,
        'description' => $speciality->description,
        'doctors' => $speciality->doctors // Liste des médecins rattachés
    ], 200);
}
}
