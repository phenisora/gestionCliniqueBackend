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
            'role'=>$request->role,
            'phone'=>$request->phone
        ]);
        if($request->role ==='patient'){
        Patient::create([
            'user_id'=>$user->id,
            'date_of_birth'=>$request->date_of_birth,
            'gender'=>$request->gender,
            'blood_type'=>$request->blood_type,
            'address'=>$request->address,
            'emergency_contact'=>$request->emergency_contact

        ]);
    }

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
                'role'=> "required |string ",
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
                    'role'=>$request->role
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

    try{
    $credential = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (auth()->attempt($credential)) {
        $user = auth()->user();


        if ($user->role === 'doctor') {

            $user->load('doctor.specialite');


        } elseif ($user->role === 'patient') {
            $user->load('patient');
        }


        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'role' => $user->role, //  utile pour le frontend
            'user' => $user,       // Contient maintenant les infos liées (doctor/patient)
        ]);
    }
}catch (\Exception $e) {
    return response()->json([
        'error' => 'Erreur lors de la création',
        'details' => $e->getMessage()
    ], 500);
}

    return response()->json(['message' => 'Identifiants incorrects'], 401);
}

public function logout(Request $request){
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Déconnexion réussie (Token supprimé)'
    ], 200);
}

public function profile(Request $request){
    $user=$request->user();
    /*
    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Non authentifié'
        ], 401);
    }
    */
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

public function updateProfile(Request $request){

}

}
