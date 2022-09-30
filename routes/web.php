<?php

use App\Http\Controllers\SearchController;
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

// Route::statamic('search/results', 'search/results', [
//    'title' => 'Search Results'
// ]);

Route::any('search/results', [SearchController::class, 'results']);
Route::any('search/getgeoloc', [SearchController::class, 'getGeoloc']);
Route::any('search/getcontent', [SearchController::class, 'getContent']);
