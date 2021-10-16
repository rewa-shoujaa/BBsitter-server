<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\BabysitterController;
use App\Http\Controllers\API\AuthController;


/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | is assigned the "api" middleware group. Enjoy building your API! | */

Route::post('/login', [AuthController::class , 'login'])->name('api:login');
Route::post('register', [AuthController::class , 'register'])->name('api:register');

Route::group(['middleware' => 'auth.jwt'], function () {
    Route::get('/test', [UserController::class , 'test'])->name('api:test');
    //Route::get('/admintest', [AdminController::class , 'test'])->name('api:admintest');
    Route::get('/getParentDetails', [UserController::class , 'getParentDetails'])->name('api:getParentDetails');
    Route::post('/setParentDetails', [UserController::class , 'setParentDetails'])->name('api:setParentDetails');
    Route::post('/SendAppointment', [UserController::class , 'SendAppointmentRequest'])->name('api:sendAppointment');
    Route::post('/getBabysittersinCity', [UserController::class , 'getBabySitterinCity'])->name('api:BabysitterinCity');

    Route::post('/babysitter/setDetails', [BabysitterController::class , 'setBabySitterDetails'])->name('api:setBabysitterDetails');
    Route::get('/babysitter/getAppointments', [BabysitterController::class , 'getAppointmentRequests'])->name('api:getAppointmentRequests');
    Route::get('/babysitter/notAvailable', [BabysitterController::class , 'setNotAvailable'])->name('api:setNotAvailable');
    Route::post('/babysitter/AcceptAppointment', [BabysitterController::class , 'AcceptAppointment'])->name('api:AcceptAppointment');

    Route::get('/admin/getParents', [AdminController::class , 'getAllParents'])->name('api:getAllParents');
    Route::get('/admin/getbabysitters', [AdminController::class , 'getAllBabysitters'])->name('api:getAllBabysitter');
    Route::get('/admin/activateParent/{id}', [AdminController::class , 'activate_parent'])->name('api:ActivateParent');
    Route::get('/admin/deactivateParent/{id}', [AdminController::class , 'deactivate_parent'])->name('api:deactivateParent');
    Route::get('/admin/activatebabysitter/{id}', [AdminController::class , 'activate_babysitter'])->name('api:activateBabysitter');
    Route::get('/admin/deactivatebabysitter/{id}', [AdminController::class , 'deactivate_babysitter'])->name('api:DeactivateBabysitter');
});

Route::group(['middleware' => 'admin'], function () {
    Route::get('/admintest', [AdminController::class , 'test'])->name('api:admintest');
});

//Route::group(['middleware' => 'babysitter'], function () {
//    Route::get('/babysitter/test', [BabysitterController::class , 'home'])->name('api:test');
//});


//Route::group(['middleware' => 'auth.jwt'], function () {
//    Route::group(['middleware' => 'admin'], function () {
//            Route::get('/admin/test', [AdminController::class , 'test'])->name('api:test');
//        }
//        );
//    })