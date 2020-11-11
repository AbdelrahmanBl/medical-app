<?php

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/ 
Route::get('/patient/get_doctors','patientsController@get_doctors');
Route::get('/patient/get_tickets','patientsController@get_tickets');
Route::group(['middleware' => 'PatientAuth'], function () { 
	Route::get('/patient/get_profile','patientsController@get_profile');
	Route::get('/patient/get_bookings','patientsController@get_bookings');
	Route::get('/patient/get_rate','patientsController@get_rate');
	/*-----------------------------------------------*/
	Route::post('/patient/update_profile','patientsController@update_profile');
	Route::post('/patient/update_profile_image','patientsController@update_profile_image');
	Route::post('/patient/book','patientsController@book');
	Route::post('/patient/rate','patientsController@rate');
	// Route::post('/message','BulkSmsController@sendSms');
});
Route::post('/patient/login','patientsController@login');
	Route::post('/patient/sent_otp','patientsController@sent_otp');
	Route::post('/patient/verify_otp','patientsController@verify_otp');
Route::post('/patient/register','patientsController@register');

Route::group(['middleware' => 'DoctorAuth'], function () {
	Route::get('/doctors/get_profile','doctorController@get_profile');
	Route::get('/doctors/get_tickets','doctorController@get_tickets');
	Route::get('/doctors/get_bookings','doctorController@get_bookings');
	Route::get('/doctors/get_rates','doctorController@get_rates');
	Route::get('/doctors/get_current_booking','doctorController@get_current_booking');
	/*-----------------------------------------------*/
	Route::post('/doctors/add_ticket','doctorController@add_ticket');
	Route::post('/doctors/tickets_status','doctorController@tickets_status');
	Route::post('/doctors/update_profile','doctorController@update_profile');
	Route::post('/doctors/update_profile_image','doctorController@update_profile_image');
	Route::post('/doctors/update_booking','doctorController@update_booking');
	Route::post('/doctors/cancel_booking','doctorController@cancel_booking');
});
Route::post('/doctors/login','doctorController@login');
Route::post('/doctors/register','doctorController@register');
Route::get('get_days','doctorController@get_days');
Route::get('get_specialities','doctorController@get_specialities');

Route::group(['middleware' => 'AdminAuth'], function () {
	Route::get('/admin/logout','adminController@logout');
	Route::get('/admin/get_profile','adminController@get_profile');
	Route::get('/admin/get_doctors_requests','adminController@get_doctors_requests');
	Route::get('/admin/get_doctors','adminController@get_doctors');
	Route::get('/admin/get_patients','adminController@get_patients');
	Route::get('/admin/get_bookings','adminController@get_bookings');
	Route::get('/admin/get_funds','adminController@get_funds');
	Route::get('/admin/get_payments','adminController@get_payments');
	Route::get('/admin/get_wallet','adminController@get_wallet');
	/*-----------------------------------------------*/
	Route::post('/admin/pay','adminController@pay');
	Route::post('/admin/update_profile','adminController@update_profile');
	Route::post('/admin/update_profile_pic','adminController@update_profile_pic');
	Route::post('/admin/approve_doctor','adminController@approve_doctor');
	Route::post('/admin/change_doctor_status','adminController@change_doctor_status');
	Route::post('/admin/change_patient_status','adminController@change_patient_status');
});
Route::post('/admin/login','adminController@login');


// Route::apiResource('/doctors','doctorController');
// Route::apiResource('/tickets','ticketController');
// Route::apiResource('/person','patientsController');