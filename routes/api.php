<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SpecialityController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PrescriptionController;





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






Route::middleware('auth:api')->group(function () {
    Route::post('/doctors', [DoctorController::class, 'store']);
    Route::put('/doctors/{doctors}', [DoctorController::class, 'update']);
    Route::delete('/doctors/{id}', [DoctorController::class, 'supprimer']);
    Route::post('/doctors/{id}/availabilities', [DoctorController::class, 'definirAvailabilities']);
    Route::get('/doctors/{id}/availabilities', [DoctorController::class, 'dispoDunmedecin']);
});



Route::group(['middleware'=>'auth:api'], function(){

    Route::GET('/patients', [PatientController::class, 'index']);
    Route::GET('/patients/{id}', [PatientController::class, 'show']);
    Route::DELETE('/patients/{id}', [PatientController::class, 'destroy']);
    Route::PUT('/patients/{id}', [PatientController::class, 'update']);
    Route::GET('/patients/{id}/medical-history', [PatientController::class, 'medicalHistory']);
    Route::GET('/patients/{id}/appointments', [PatientController::class, 'appointments']);



});



Route::post('/auth/register', [AuthController::class, 'registerPatient']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware(['auth:api'])->group(function () {



});

Route::post('/auth/register', [AuthController::class, 'registerPatient']);
Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware(['auth:api'])->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::put('auth/updateProfile', [AuthController::class, 'updateProfile']);


    Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);
    Route::get('/appointments/date/{date}', [AppointmentController::class, 'byDate']);
    Route::get('/appointments/doctors/{id}/appointment', [AppointmentController::class, 'doctorAppointments']);
    Route::get('/specialities', [SpecialityController::class, 'index']);
    Route::get('/specialities/{id}', [SpecialityController::class, 'show']);


    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::put('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
    Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);





    // Routes Réceptionniste
    Route::middleware(['role:receptionist'])->group(function () {
    Route::post('auth/register/doctor', [AuthController::class, 'registerDoctor']);
    Route::apiResource('patients', PatientController::class);
    });
});



//Route pour les ordonnances

Route::post('/prescriptions',[PrescriptionController::class,'creerOrdonnance']);
Route::put('/prescriptions/{id}',[PrescriptionController::class,'modifierOrdonnance']);
Route::delete('/prescriptions/{id}',[PrescriptionController::class,'supprimerOrdonnance']);
Route::get('/mesprescriptions',[PrescriptionController::class,'mesOrdonnance']);
Route::get('/prescriptions/{id}',[PrescriptionController::class,'detailsOrdonnance']);
Route::get('/appointments/{id}/prescriptions',[PrescriptionController::class,'ordonnancesRDV']);

