<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
=======

use App\Http\Controllers\AuthController;


>>>>>>> 2c9541720b0fe031ea93e59a9a71fd21463abb34
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;




<<<<<<< HEAD
=======




>>>>>>> 2c9541720b0fe031ea93e59a9a71fd21463abb34
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Routes public pour les médecins
Route::get('/doctors',[DoctorController::class,'index']);
Route::get('/doctors/{doctors}',[DoctorController::class,'detail']);
Route::get('/doctors/specialty/{id}', [DoctorController::class, 'medecinsParSpecialite']);
// Route pour la recherche  un docteur par son nom
Route::get('/doctors/search', [DoctorController::class, 'searchParNom']);
// Route pour la recherche par date
Route::get('/doctors/available', [DoctorController::class, 'availableParDate']);

// Routes Protégées (Médecin/Réceptionniste)

<<<<<<< HEAD
Route::middleware('auth:sanctum')->group(function () {
=======

Route::middleware('auth:api')->group(function () {


//Route::middleware('auth:sanctum')->group(function () {


//Route::middleware('auth:sanctum')->group(function () {

>>>>>>> 2c9541720b0fe031ea93e59a9a71fd21463abb34
    Route::post('/doctors', [DoctorController::class, 'store']);
    Route::put('/doctors/{doctors}', [DoctorController::class, 'update']);
    Route::delete('/doctors/{id}', [DoctorController::class, 'supprimer']);
    Route::post('/doctors/{id}/availabilities', [DoctorController::class, 'definirAvailabilities']);
    Route::get('/doctors/{id}/availabilities', [DoctorController::class, 'dispoDunmedecin']);
});


<<<<<<< HEAD
Route::group(['middleware'=>'auth:sanctum'], function(){

=======

Route::group(['middleware'=>'auth:api'], function(){
>>>>>>> 2c9541720b0fe031ea93e59a9a71fd21463abb34
    Route::GET('/patients', [PatientController::class, 'index']);
    Route::GET('/patients/{id}', [PatientController::class, 'show']);
    Route::DELETE('/patients/{id}', [PatientController::class, 'destroy']);
    Route::PUT('/patients/{id}', [PatientController::class, 'update']);
    Route::GET('/patients/{id}/medical-history', [PatientController::class, 'medicalHistory']);
    Route::GET('/patients/{id}/appointments', [PatientController::class, 'appointments']);
<<<<<<< HEAD

});

=======
});
>>>>>>> 2c9541720b0fe031ea93e59a9a71fd21463abb34

Route::post('/auth/register', [AuthController::class, 'registerPatient']);
Route::post('/auth/login', [AuthController::class, 'login']);


<<<<<<< HEAD
Route::middleware(['auth:sanctum'])->group(function () {
=======
Route::middleware(['auth:api'])->group(function () {

>>>>>>> 2c9541720b0fe031ea93e59a9a71fd21463abb34
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::put('auth/updateProfile', [AuthController::class, 'updateProfile']);

    // Routes Réceptionniste
    Route::middleware(['role:receptionist'])->group(function () {
    Route::post('auth/register/doctor', [AuthController::class, 'registerDoctor']);
    Route::apiResource('patients', PatientController::class);
    });
});

<<<<<<< HEAD
=======


//Route pour les ordonnances

Route::post('/prescriptions',[PrescriptionController::class,'creerOrdonnance']);
Route::put('/prescriptions/{id}',[PrescriptionController::class,'modifierOrdonnance']);
Route::delete('/prescriptions/{id}',[PrescriptionController::class,'supprimerOrdonnance']);
>>>>>>> 2c9541720b0fe031ea93e59a9a71fd21463abb34
