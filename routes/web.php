<?php

use App\Models\User;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Test routes for Google Sheets integration (only available in local environment)
Route::prefix('test/sheets')
    ->middleware(['local'])
    ->group(function () {
    // Test connection to Google Sheets
    Route::get('/test-connection', function () {
        try {
            $service = app(GoogleSheetsService::class);
            $data = $service->getData();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully connected to Google Sheets',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to Google Sheets',
                'error' => $e->getMessage()
            ], 500);
        }
    });

    // Test adding data to Google Sheets
    Route::get('/test-add-data', function () {
        try {
            $service = app(GoogleSheetsService::class);
            $stats = $service->collectStatistics();
            $result = $service->addStatistics($stats);
            
            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully added data to Google Sheets',
                    'data' => $stats
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add data to Google Sheets'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while adding data to Google Sheets',
                'error' => $e->getMessage()
            ], 500);
        }
    });

    // Initialize Google Sheets with headers
    Route::get('/init-sheet', function () {
        try {
            $service = app(GoogleSheetsService::class);
            $headers = [
                'Date & Time',
                'Total Users',
                'Active Users (Last 30 days)',
                'Total Videos',
                'Total Quizzes',
                'Total Attendance Records',
                'Total Recitation Records',
            ];
            
            $result = $service->initializeHeaders($headers);
            
            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully initialized Google Sheet with headers'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to initialize Google Sheet headers'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while initializing Google Sheet',
                'error' => $e->getMessage()
            ], 500);
        }
    });
});

// Default route
Route::get('/', function () {
    return view('welcome');
});
