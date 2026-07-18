<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// روابط عامة (لا تحتاج تسجيل دخول)
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::get('/news', [\App\Http\Controllers\Api\DataController::class, 'getNews']);

// روابط محمية (نستخدم نظامنا اليدوي بدلاً من Sanctum المكسور)
Route::middleware('manual.auth')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/recitations', [\App\Http\Controllers\Api\DataController::class, 'getRecitations']);
    Route::get('/next-recitations', [\App\Http\Controllers\Api\DataController::class, 'getNextRecitations']);
    Route::get('/homeworks', [\App\Http\Controllers\Api\DataController::class, 'getHomeworks']);
    Route::get('/videos', [\App\Http\Controllers\Api\DataController::class, 'getVideos']);
    Route::get('/files', [\App\Http\Controllers\Api\DataController::class, 'getFiles']);
    Route::get('/attendance', [\App\Http\Controllers\Api\DataController::class, 'getAttendance']);
    Route::get('/quizzes', [\App\Http\Controllers\Api\DataController::class, 'getQuizzes']);
    Route::get('/notifications', [\App\Http\Controllers\Api\DataController::class, 'getNotifications']);
    Route::post('/notifications/mark-read', [\App\Http\Controllers\Api\DataController::class, 'markNotificationsRead']);
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
});
