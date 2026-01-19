<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Routes public pour les médecins
Route::get('/doctors',[DoctorController::class,'index']);
Route::get('/doctors/{doctors}',[DoctorController::class,'detail']);
Route::get('/doctors/specialty/{id}', [DoctorController::class, 'doctorsParpecialty']);
Route::get('/doctors/{id}/availabilities', [DoctorController::class, 'voirAvailabilities']);

// Routes Protégées (Médecin/Réceptionniste) 

Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/doctors', [DoctorController::class, 'store']);
    Route::put('/doctors/{id}', [DoctorController::class, 'update']);
    Route::delete('/doctors/{id}', [DoctorController::class, 'supprimer']);
    Route::post('/doctors/{id}/availabilities', [DoctorController::class, 'definirAvailabilities']);
});

Route::post('/auth/register', [AuthController::class, 'registerPatient']);
Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {
    // Routes Réceptionniste
    Route::middleware(['role:receptionist'])->group(function () {
        Route::post('auth/register/doctor', [AuthController::class, 'registerDoctor']);
       // Route::apiResource('patients', PatientController::class);
    });
});

