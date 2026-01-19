<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


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
Route::post('/auth/register', [AuthController::class, 'registerPatient']);
Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {
    // Routes Réceptionniste
    Route::middleware(['role:receptionist'])->group(function () {
        Route::post('auth/register/doctor', [AuthController::class, 'registerDoctor']);
       // Route::apiResource('patients', PatientController::class);
    });
});
>>>>>>> fbb02b984c09a97d624fea6faf7f698e6d10dc6c
