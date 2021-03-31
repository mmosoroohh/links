<?php

use Illuminate\Support\Facades\Route;


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

//Route::get('/', function () {
//    return view('welcome');
//});
//
Auth::routes(['verify' => true ]);
//
//Route::get('/home', 'HomeController@index')->name('home');
//Route::get('/', function (){
//    $links = \App\Models\Link::all();
//
//    return view('welcome', ['links' => $links]);
//    // with()
//    return view('welcome')->with('links', $links);
//
//// dynamic method to name the variable
//    return view('welcome')->withLinks($links);

Route::get('/', 'LinkController@index');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/submit', 'LinkController@create');
Route::post('/submit', 'LinkController@store');



