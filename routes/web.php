<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\MSGraphController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\APITest;
use GuzzleHttp\Middleware;
use Illuminate\Routing\Route as RoutingRoute;
use Symfony\Component\Routing\Route as ComponentRoutingRoute;

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

Route::get('/', [AuthController::class, 'admin_area']);
Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'login_process']);
Route::get('/logout', [AuthController::class, 'logOut']);
Route::get('/msgraph-api', [MSGraphController::class, 'api_response']);

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/api-call', [APIController::class, 'admin_trigger_manually']); 
    Route::get('/admin/auth-delete', [APIController::class, 'delete']);
});