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

Route::get('/api/Sheets_API/refreshSheetValues/{id}', 'GoogleSheetsController@refreshSheetValues');
Route::get('/api/Sheets_API/populateSpeadsheet/{id}', 'GoogleSheetsController@populateSpreadsheet');
Route::get('/api/Sheets_API/setBackgroundColor/{id}', 'GoogleSheetsController@setBackgroundColor');
Route::get('/api/Sheets_API/disableCells/{id}', 'GoogleSheetsController@disableCells');
Route::get('/api/Sheets_API/addFrozenRow/{id}', 'GoogleSheetsController@addFrozenRow');
Route::get('/api/Sheets_API/setHorizontalAlignment/{id}', 'GoogleSheetsController@setHorizontalAlignment');

Route::get('/api/Sheets_API/test/{id}', 'GoogleSheetsController@test');
