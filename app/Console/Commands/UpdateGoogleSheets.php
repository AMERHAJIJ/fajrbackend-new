<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateGoogleSheets extends Command
{
    protected $signature = 'sheets:update {--init : Initialize the sheet with headers} {--clear : Clear all data from the sheet}';
    protected $description = 'Update Google Sheets with the latest statistics';
    protected $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        parent::__construct();
        $this->googleSheetsService = $googleSheetsService;
    }

    public function handle()
    {
        try {
            if ($this->option('clear')) {
                $this->clearSheet();
                return 0;
            }

            if ($this->option('init')) {
                $this->initializeSheet();
                return 0;
            }

            $this->updateStatistics();
            return 0;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Google Sheets update failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function initializeSheet()
    {
        $headers = [
            'Date & Time',
            'Total Users',
            'Active Users (Last 30 days)',
            'Total Videos',
            'Total Quizzes',
            'Total Attendance Records',
            'Total Recitation Records',
        ];

        $result = $this->googleSheetsService->initializeHeaders($headers);

        if ($result) {
            $this->info('Successfully initialized Google Sheet with headers.');
        } else {
            $this->error('Failed to initialize Google Sheet headers.');
        }
    }

    protected function updateStatistics()
    {
        $statistics = $this->googleSheetsService->collectStatistics();
        $result = $this->googleSheetsService->addStatistics($statistics);

        if ($result) {
            $this->info('Successfully updated Google Sheet with latest statistics.');
            $this->table(
                ['Metric', 'Value'],
                collect($statistics)->map(function ($value, $key) {
                    return [
                        'Metric' => ucwords(str_replace('_', ' ', $key)),
                        'Value' => $value
                    ];
                })->toArray()
            );
        } else {
            $this->error('Failed to update Google Sheet with statistics.');
        }
    }

    protected function clearSheet()
    {
        if ($this->confirm('Are you sure you want to clear all data from the sheet?')) {
            $result = $this->googleSheetsService->clearSheet(false);
            
            if ($result) {
                $this->info('Successfully cleared all data from Google Sheet.');
            } else {
                $this->error('Failed to clear data from Google Sheet.');
            }
        }
    }
}
