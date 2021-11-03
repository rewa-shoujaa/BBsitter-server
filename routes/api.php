<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\BabysitterController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\WebNotificationController;


/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider within a group which | is assigned the "api" middleware group. Enjoy building your API! | */

Route::post('/login', [AuthController::class , 'login'])->name('api:login');
Route::post('/register/parent', [UserController::class , 'register_user'])->name('api:register_parent');
Route::get('/getCountries', [UserController::class , 'getCountries'])->name('api:countries');
Route::get('/getCities/{Country}', [UserController::class , 'getCities'])->name('api:cities');
Route::post('/babysitter/register', [BabysitterController::class , 'register_babysitter'])->name('api:RegisterBabysitter');


Route::group(['middleware' => 'auth.jwt'], function () {
    Route::get('/test', [UserController::class , 'test'])->name('api:test');
    //Route::get('/admintest', [AdminController::class , 'test'])->name('api:admintest');
    Route::get('/getParentDetails', [UserController::class , 'getParentDetails'])->name('api:getParentDetails');
    Route::post('/setParentDetails', [UserController::class , 'setParentDetails'])->name('api:setParentDetails');
    Route::post('/SendAppointment', [UserController::class , 'SendAppointmentRequest'])->name('api:sendAppointment');
    Route::get('/getBabysittersinCity/{CityID}', [UserController::class , 'getBabysittersinCity'])->name('api:BabysitterinCity');
    Route::post('/BookAppointment', [UserController::class , 'BookAppointment'])->name('api:bookAppointmnet');
    Route::get('/babysitterDetails/{id}', [UserController::class , 'getBabysitterDetails'])->name('api:getbabysitterProfile');
    Route::get('/getAllBabysitters', [UserController::class , 'getAllBabysitters'])->name('api:getBabysitters');

    Route::get('/babysitterDetailswithRating/{id}', [UserController::class , 'getBabysitterDetailswithRatings'])->name('api:getBabysitterDetailswithRatings');


    Route::post('/search', [UserController::class , 'Search'])->name('api:Search');
    Route::get('/getScheduled', [UserController::class , 'getScheduled'])->name('api:getScheduled');
    Route::get('/getPending', [UserController::class , 'getPending'])->name('api:getPending');
    Route::get('/Cancel/{id}', [UserController::class , 'Cancel'])->name('api:Cancel');
    Route::post('/FeelingLucky', [UserController::class , 'FeelingLucky'])->name('api:FeelingLucky');
    Route::post('/rate', [UserController::class , 'AddRating'])->name('api:AddRating');

    Route::get('/babysitter/getAppointments', [BabysitterController::class , 'getAppointmentRequests'])->name('api:getAppointmentRequests');
    Route::get('/babysitter/AcceptAppointment/{id}', [BabysitterController::class , 'AcceptAppointment'])->name('api:AcceptAppointment');
    Route::get('/babysitter/DeclineAppointment/{id}', [BabysitterController::class , 'DeclineAppointment'])->name('api:DeclineAppointment');
    Route::get('/babysitter/getScheduled', [BabysitterController::class , 'getAppointmentScheduled'])->name('api:getAppointmentScheduled');



    Route::get('/babysitter/getDetails', [BabysitterController::class , 'getBabysitterDetails'])->name('api:getBabysitterDetails');
    Route::post('/babysitter/setDetails', [BabysitterController::class , 'setBabySitterDetails'])->name('api:setBabysitterDetails');
    //Route::get('/babysitter/getAppointments', [BabysitterController::class , 'getAppointmentRequests'])->name('api:getAppointmentRequests');
    Route::get('/babysitter/notAvailable', [BabysitterController::class , 'setNotAvailable'])->name('api:setNotAvailable');
    //Route::post('/babysitter/AcceptAppointment', [BabysitterController::class , 'AcceptAppointment'])->name('api:AcceptAppointment');

    ///Admin
    Route::get('/admin/getParents', [AdminController::class , 'getAllParents'])->name('api:getAllParents');
    Route::get('/admin/getbabysitters', [AdminController::class , 'getAllBabysitters'])->name('api:getAllBabysitter');
    Route::get('/admin/activateParent/{id}', [AdminController::class , 'activate_parent'])->name('api:ActivateParent');
    Route::get('/admin/deactivateParent/{id}', [AdminController::class , 'deactivate_parent'])->name('api:deactivateParent');
    Route::get('/admin/activatebabysitter/{id}', [AdminController::class , 'activate_babysitter'])->name('api:activateBabysitter');
    Route::get('/admin/deactivatebabysitter/{id}', [AdminController::class , 'deactivate_babysitter'])->name('api:DeactivateBabysitter');

    ///Notification
    Route::post('/save-device-token', [WebNotificationController::class , 'saveDeviceToken'])->name('save-device-token');
    Route::get('/sendNotification/{device_token}/{message}', [WebNotificationController::class , 'sendNotification'])->name('sendNotification');
    Route::get('/getNotifications', [WebNotificationController::class , 'getNotifications'])->name('getNotifications');
    Route::get('/setRead', [WebNotificationController::class , 'setRead'])->name('setRead');
    Route::get('/sendlocalNotification/{notification_body}/{targetID}', [WebNotificationController::class , 'sendlocalNotification'])->name('sendlocalNotification');

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