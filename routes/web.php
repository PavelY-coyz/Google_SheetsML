<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/projects', 'PageController@getProjects')->name('projects');
Route::get('/testPHP', 'TestPHPController@test');

Route::get('/projects/{id}', 'GoogleSheetsController@getGoogleSheets');
Route::get('/projects/google_sheets_sample1/{id}', 'GoogleSheetsController@getGoogleSheets');
Route::get('/projects/google_sheets_sample3/{id}', 'GoogleSheetsController@getGoogleSheets');
Route::get('/projects/google_sheets_sample5/{id}', 'GoogleSheetsController@getGoogleSheets');

Route::get('/api/Sheets_API/refreshValuesRequest/{id}', 'GoogleSheetsController@refreshValuesRequest');
Route::get('/api/Sheets_API/setBackgroundColorRequest/{id}', 'GoogleSheetsController@setBackgroundColorRequest');
Route::get('/api/Sheets_API/disableCellsRequest/{id}', 'GoogleSheetsController@disableCellsRequest');
Route::get('/api/Sheets_API/addFrozenRowRequest/{id}', 'GoogleSheetsController@addFrozenRowRequest');
Route::get('/api/Sheets_API/setHorizontalAlignmentRequest/{id}', 'GoogleSheetsController@setHorizontalAlignmentRequest');
Route::get('/api/Sheets_API/setCellFormatRequest/{id}', 'GoogleSheetsController@setCellFormatRequest');
Route::post('/api/Sheets_API/batchUpdate', 'GoogleSheetsController@batchUpdate');

//Will be removed
Route::get('/api/Sheets_API/populateSpeadsheet/{id}', 'GoogleSheetsController@populateSpreadsheet');

//testing function
Route::get('/api/Sheets_API/test/{id}', 'GoogleSheetsController@test');