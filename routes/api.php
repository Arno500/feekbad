<?php

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

Route::get('/feedbacks/get', 'FeedbackController@get');
//Route::post('/feedbacks/post', 'FeedbackController@post')->middleware('jwt');
Route::patch('/feedbacks/{id}', 'FeedbackController@put')->where('id', '[0-9]+')->middleware('jwt');
Route::delete('/feedbacks/{id}', 'FeedbackController@delete')->where('id', '[0-9]+')->middleware('jwt');
Route::put('/feedbacks', 'FeedbackController@put')->middleware('jwt');
