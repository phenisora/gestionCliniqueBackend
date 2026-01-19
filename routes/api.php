<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use App\Http\Controllers\DoctorController;
=======
use App\Http\Controllers\PatientController;

>>>>>>> 77ebcd5abc4c3f6e0ad4ffb68e23f420d1926b02

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


<<<<<<< HEAD
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

=======
<<<<<<< HEAD
//Route::group(['middleware'=>'auth:sanctum'], function(){
    Route::GET('/patients', [PatientController::class, 'index']);
    Route::GET('/patients/{id}', [PatientController::class, 'show']);
    Route::DELETE('/patients/{id}', [PatientController::class, 'destroy']);
    Route::PUT('/patients/{id}', [PatientController::class, 'update']);
    Route::GET('/patients/{id}/medical-history', [PatientController::class, 'medicalHistory']);
    Route::GET('/patients/{id}/appointments', [PatientController::class, 'appointments']);
//});
=======
>>>>>>> 77ebcd5abc4c3f6e0ad4ffb68e23f420d1926b02
Route::post('/auth/register', [AuthController::class, 'registerPatient']);
Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {
    // Routes Réceptionniste
    Route::middleware(['role:receptionist'])->group(function () {
        Route::post('auth/register/doctor', [AuthController::class, 'registerDoctor']);
       // Route::apiResource('patients', PatientController::class);
    });
});
<<<<<<< HEAD

=======
>>>>>>> fbb02b984c09a97d624fea6faf7f698e6d10dc6c
>>>>>>> 77ebcd5abc4c3f6e0ad4ffb68e23f420d1926b02
