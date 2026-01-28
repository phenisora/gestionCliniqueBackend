<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function registerPatient(Request $request){

        try{
            $request->validate([
            'email'=>'required|unique:users'
        ]);

        $result = DB::transaction(function () use ($request) {
        $user= User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
            'role' => 'patient',
            'phone'=>$request->phone
        ]);
        
        Patient::create([
            'user_id'=>$user->id,
            'date_of_birth'=>$request->date_of_birth,
            'gender'=>$request->gender,
            'blood_type'=>$request->blood_type,
            'address'=>$request->address,
            'emergency_contact'=>$request->emergency_contact

        ]);
    

    });
        return response()->json([
            'message'=>'inscription reussi, cher patient'

        ]);
    }catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la création',
            'details' => $e->getMessage()
        ], 500);
    }
}

    public function registerDoctor(Request $request){
        try {

            $request->validate([
                'email'=> "required|unique:users",
                'name'=> " required |  string",
                'password'=> " required | string",
                'specialty_id'=>"required|exists:specialities,id",
                'license_number'=>"required|unique:doctors,license_number",
                'consultation_fee'=>"required",
                'bio'=>"required"
            ]);

            $result = DB::transaction(function () use ($request) {
                $user= User::create([
                    'name'=>$request->name,
                    'email'=>$request->email,
                    'password'=>bcrypt($request->password),
                    'phone'=>$request->phone,
                    'role' => 'doctor'
                ]);

                Doctor::create([
                    'user_id'=>$user->id,
                    'specialty_id'=>$request['specialty_id'],
                    'license_number'=>$request->license_number,
                    'consultation_fee'=>$request->consultation_fee,
                    'bio'=>$request->bio
                ]);


        });
        return response()->json([
            'message'=>'inscription Doctor, reussi'

        ]);
    }catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création',
                'details' => $e->getMessage() ,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }



}

public function login(Request $request) {

    try {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // auth()->attempt() vérifie les identifiants et génère le token JWT
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }
        
        $user = auth('api')->user();
        

        // Chargement des relations selon le rôle
        if ($user->role === 'doctor') {
            $user->load('doctor.speciality');
        } elseif ($user->role === 'patient') {
            $user->load('patient');
        }

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token, 
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60, // Durée de vie en secondes
            'role' => $user->role,
            'user' => $user,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la connexion',
            'details' => $e->getMessage()
        ], 500);
    }
}

public function logout() {
    auth('api')->logout();
    return response()->json(['message' => 'Déconnexion réussie (Token invalidé)']);
}

public function profile(Request $request){

    $user = auth('api')->user();
if (!$user) {
    return response()->json(['status' => false, 'message' => 'Non authentifié'], 401);
}

    try{

    if ($user->role === 'doctor'){
        $user->load('doctor.speciality');
    } elseif ($user->role === 'patient') {
        $user->load('patient');
    }
}catch (\Exception $e) {
    return response()->json([
        'error' => 'Erreur lors du chargement du profile',
        'details' => $e->getMessage()
    ], 500);
}

    return response()->json([
        'status' => true,
        'data' => $user
    ]);
}

public function updateProfile(Request $request)
{
    $user = auth('api')->user();

    if (!$user) {
        return response()->json(['message' => 'Non authentifié'], 401);
    }

    // règles de validation de base
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'phone' => 'nullable|string',
        'password' => 'nullable|min:6|confirmed', 
    ];

    // Ajout des règles spécifiques selon le rôle
    if ($user->role === 'doctor' && $user->doctor) {
        // On ignore l'ID du docteur actuel pour la licence unique
        $rules['license_number'] = 'required|unique:doctors,license_number,' . $user->doctor->id;
        $rules['specialty_id'] = 'sometimes|exists:specialities,id';
        $rules['consultation_fee'] = 'sometimes|numeric';
    }

    $validatedData = $request->validate($rules);

    try {
        return DB::transaction(function () use ($request, $user) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];

            // Si un nouveau mot de passe est fourni, on le hache
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            $user->update($userData);

            if ($user->role === 'doctor' && $user->doctor) {
                $user->doctor->update($request->only([
                    'specialty_id', 
                    'license_number', 
                    'consultation_fee',
                    'bio'
                ]));
            } 
            elseif ($user->role === 'patient' && $user->patient) {
                $user->patient->update($request->only([
                    'address', 
                    'date_of_birth',
                    'blood_type',
                    'emergency_contact'
                ]));
            }

            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'user' => $user->load($user->role === 'doctor' ? 'doctor.speciality' : 'patient')
            ]);
        });
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Échec de la mise à jour',
            'details' => $e->getMessage()
        ], 500);
    }
}

}
