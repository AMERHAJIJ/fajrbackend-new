<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected $service;
    protected $spreadsheetId;
    protected $sheetName;

    public function __construct()
    {
        try {
            $client = new Client();
            $client->setScopes([
                'https://www.googleapis.com/auth/spreadsheets',
                'https://www.googleapis.com/auth/drive'
            ]);
            
            // Set the service account credentials
            $serviceAccountPath = storage_path('app/public/google-service-account.json');
            
            if (!file_exists($serviceAccountPath)) {
                // Try alternative path
                $serviceAccountPath = config('services.google_sheets.service_account_path');
                if (!file_exists($serviceAccountPath)) {
                    throw new \Exception('Google Service Account file not found. Checked paths: ' . 
                        storage_path('app/public/google-service-account.json') . ' and ' . $serviceAccountPath);
                }
            }
            
            $client->setAuthConfig($serviceAccountPath);
            
            $this->service = new Sheets($client);
            $this->spreadsheetId = config('services.google_sheets.spreadsheet_id');
            $this->sheetName = config('services.google_sheets.sheet_name');
            
            // Validate configuration
            if (empty($this->spreadsheetId)) {
                throw new \Exception('Google Sheets Spreadsheet ID is not configured');
            }
            
            if (empty($this->sheetName)) {
                throw new \Exception('Google Sheets Sheet Name is not configured');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Sheets Service: ' . $e->getMessage());
            Log::error('Spreadsheet ID: ' . $this->spreadsheetId ?? 'null');
            Log::error('Sheet Name: ' . $this->sheetName ?? 'null');
            Log::error('Service Account Path: ' . $serviceAccountPath ?? 'null');
            throw $e;
        }
    }

    /**
     * Convert column number to letter (1=A, 2=B, etc.)
     */
    private function numberToColumnLetter(int $number): string
    {
        $letter = '';
        while ($number > 0) {
            $number--;
            $letter = chr(65 + ($number % 26)) . $letter;
            $number = intval($number / 26);
        }
        return $letter;
    }

    /**
     * Simple update data to Google Sheets
     */
    private function simpleUpdateData(array $rows): bool
    {
        try {
            // Get existing data to find the next row
            $existingData = $this->getData();
            $nextRow = count($existingData) + 1;
            
            // Calculate range for new data
            $endRow = $nextRow + count($rows) - 1;
            $range = $this->sheetName . '!A' . $nextRow . ':F' . $endRow;
            
            Log::info('Simple update data to Google Sheets:', [
                'spreadsheet_id' => $this->spreadsheetId,
                'range' => $range,
                'next_row' => $nextRow,
                'end_row' => $endRow,
                'rows_count' => count($rows),
                'data' => $rows
            ]);
            
            $body = new ValueRange([
                'values' => $rows
            ]);
            
            $params = ['valueInputOption' => 'USER_ENTERED'];
            
            $response = $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );
            
            Log::info('Simple update response:', [
                'response' => $response->toSimpleObject()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to simple update data to Google Sheets: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Append new data to Google Sheets without clearing existing data
     */
    private function appendData(array $rows): bool
    {
        try {
            // Use append which automatically adds data at the end
            $range = $this->sheetName . '!A:F'; // Use column range
            
            Log::info('Appending data to Google Sheets (preserves existing data):', [
                'spreadsheet_id' => $this->spreadsheetId,
                'range' => $range,
                'rows_count' => count($rows),
                'data' => $rows
            ]);
            
            $body = new ValueRange([
                'values' => $rows
            ]);
            
            $params = [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS' // This ensures data is appended, not overwritten
            ];
            
            $response = $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );
            
            Log::info('Google Sheets append response (data added at end):', [
                'response' => $response->toSimpleObject()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to append data to Google Sheets: ' . $e->getMessage());
            Log::error('Append error details:', [
                'spreadsheet_id' => $this->spreadsheetId,
                'range' => $range ?? 'unknown',
                'rows_count' => count($rows)
            ]);
            return false;
        }
    }

    /**
     * Batch update data to Google Sheets
     */
    private function batchUpdateData(array $rows): bool
    {
        try {
            $requests = [];
            
            // Add each row individually to avoid range issues
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Start from row 2 (after headers)
                $range = $this->sheetName . '!A' . $rowNumber . ':F' . $rowNumber;
                
                $requests[] = new \Google\Service\Sheets\Request([
                    'updateCells' => [
                        'range' => [
                            'sheetId' => 0, // First sheet
                            'startRowIndex' => $rowNumber - 1,
                            'endRowIndex' => $rowNumber,
                            'startColumnIndex' => 0,
                            'endColumnIndex' => 6, // 6 columns (A to F)
                        ],
                        'rows' => [
                            [
                                'values' => array_map(function($value) {
                                    return ['userEnteredValue' => ['stringValue' => (string)$value]];
                                }, $row)
                            ]
                        ],
                        'fields' => 'userEnteredValue'
                    ]
                ]);
            }
            
            $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);
            
            $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to batch update data: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test if a range is valid
     */
    public function testRange(string $range): bool
    {
        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            return true;
        } catch (\Exception $e) {
            Log::error('Invalid range: ' . $range . ' - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test connection to Google Sheets
     */
    public function testConnection(): bool
    {
        try {
            // Try to get spreadsheet metadata
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
            Log::info('Google Sheets connection successful. Spreadsheet title: ' . $spreadsheet->getProperties()->getTitle());
            return true;
        } catch (\Exception $e) {
            Log::error('Google Sheets connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Initialize the sheet with headers
     */
    public function initializeHeaders(array $headers): bool
    {
        try {
            // Calculate the correct range based on number of headers
            $endColumn = count($headers);
            $endColumnLetter = $this->numberToColumnLetter($endColumn);
            $range = $this->sheetName . '!A1:' . $endColumnLetter . '1';
            
            $body = new ValueRange([
                'values' => [$headers]
            ]);
            
            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];
            
            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Sheets headers: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a new row to the sheet
     */
    public function addRow(array $data): bool
    {
        try {
            $range = $this->sheetName . '!A:A';
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            $values = $response->getValues();
            
            $nextRow = empty($values) ? 1 : count($values) + 1;
            
            $range = $this->sheetName . '!A' . $nextRow . ':Z' . $nextRow;
            $body = new ValueRange([
                'values' => [$data]
            ]);
            
            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];
            
            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to add row to Google Sheets: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all data from the sheet
     */
    public function getData(): array
    {
        try {
            $range = $this->sheetName . '!A:Z';
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            return $response->getValues() ?: [];
        } catch (\Exception $e) {
            Log::error('Failed to get data from Google Sheets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear all data from the sheet (except headers if specified)
     */
    public function clearSheet(bool $keepHeaders = true): bool
    {
        try {
            // First check if there's any data to clear
            $existingData = $this->getData();
            if (empty($existingData)) {
                // No data to clear
                return true;
            }
            
            // Use a safer range for clearing - only clear from row 2 onwards
            $range = $this->sheetName . '!A2:F'; // Clear from row 2 to end of column F
            
            if (!$keepHeaders) {
                $range = $this->sheetName . '!A1:F'; // Clear from row 1 to end of column F
            }
            
            // Use batchClear instead of update with empty values
            $this->service->spreadsheets_values->batchClear(
                $this->spreadsheetId,
                new \Google\Service\Sheets\BatchClearValuesRequest([
                    'ranges' => [$range]
                ])
            );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear Google Sheet: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update student report in Google Sheet
     * 
     * @param int $subjectId
     * @param string $date
     * @return bool
     */
    public function updateStudentReport(int $subjectId, string $date): bool
    {
        try {
            // Test connection first
            if (!$this->testConnection()) {
                throw new \Exception('لا يمكن الاتصال بـ Google Sheets. تحقق من Spreadsheet ID وصلاحيات Service Account.');
            }
            // Get students with their attendance, recitation records, and next recitations
            $students = \App\Models\User::role('student')
                ->whereHas('subjectsAsStudent', function($query) use ($subjectId) {
                    $query->where('subject_id', $subjectId);
                })
                ->with([
                    'attendances' => function($query) use ($date) {
                        $query->whereDate('date', $date);
                    },
                    'recitationRecords' => function($query) use ($date) {
                        $query->whereDate('date', $date);
                    },
                    'nextRecitations.surah'
                ])
                ->get();

            // Prepare headers if sheet is empty
            $headers = [
                'اسم الطالب',
                'الحضور',
                'ما سمع',
                'النتيجة',
                'ملاحظات',
                'تاريخ اليوم'
            ];

            // Initialize sheet with headers if needed
            $existingData = $this->getData();
            if (empty($existingData)) {
                $this->initializeHeaders($headers);
            } else {
                // If headers exist but are different, clear the sheet and add new headers
                $existingHeaders = $existingData[0] ?? [];
                if ($existingHeaders != $headers) {
                    $this->clearSheet(false);
                    $this->initializeHeaders($headers);
                }
            }

            // Prepare data for Google Sheets
            $rows = [];
            
            foreach ($students as $student) {
                $attendance = $student->attendances->first();
                $recitationRecord = $student->recitationRecords->first();

                // Get recitation info
                $recitationInfo = 'لا يوجد';
                if ($recitationRecord && $recitationRecord->surahs->isNotEmpty()) {
                    $recitationInfo = $recitationRecord->surahs->map(function($surah) {
                        return "{$surah->name} (من آية {$surah->pivot->fromAyeh} إلى {$surah->pivot->toAyeh})";
                    })->implode('، ');
                }

                // Format attendance status
                $attendanceStatus = 'لم يتم التسجيل';
                if ($attendance) {
                    $attendanceStatus = $attendance->status ? 'حاضر' : 'غائب';
                }

                // Format recitation info
                $formattedRecitationInfo = 'لا يوجد';
                if ($recitationRecord && $recitationRecord->surahs->isNotEmpty()) {
                    $formattedRecitationInfo = $recitationRecord->surahs->map(function($surah) {
                        return "{$surah->name} (من آية {$surah->pivot->fromAyeh} إلى {$surah->pivot->toAyeh})";
                    })->implode('، ');
                }

                // Format score
                $formattedScore = 'لا يوجد';
                if ($recitationRecord && $recitationRecord->score) {
                    $formattedScore = $recitationRecord->score . '/10';
                }

                // Format notes
                $formattedNotes = '';
                if ($recitationRecord && $recitationRecord->notes) {
                    $formattedNotes = $recitationRecord->notes;
                }

                $row = [
                    $student->name ?? '',
                    $attendanceStatus,
                    $formattedRecitationInfo,
                    $formattedScore,
                    $formattedNotes,
                    $date
                ];

                $rows[] = $row;
            }

            // Log data for debugging
            Log::info('Student report data prepared:', [
                'subject_id' => $subjectId,
                'date' => $date,
                'students_count' => $students->count(),
                'rows_count' => count($rows),
                'rows_data' => $rows
            ]);

            // Add new data without clearing existing data
            if (!empty($rows)) {
                Log::info('Attempting to add data to Google Sheets...');
                
                // Use append to add data at the end (this preserves existing data)
                $result = $this->appendData($rows);
                Log::info('Append result: ' . ($result ? 'success' : 'failed'));
                
                // If append fails, try simple update
                if (!$result) {
                    Log::info('Append failed, trying simple update...');
                    $result = $this->simpleUpdateData($rows);
                    Log::info('Simple update result: ' . ($result ? 'success' : 'failed'));
                }
                
                // If simple update fails, try batch update
                if (!$result) {
                    Log::info('Simple update failed, trying batch update...');
                    $result = $this->batchUpdateData($rows);
                    Log::info('Batch update result: ' . ($result ? 'success' : 'failed'));
                }
            } else {
                Log::warning('No data to add - rows array is empty');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update student report in Google Sheets: ' . $e->getMessage());
            Log::error('Error details: ' . json_encode([
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'spreadsheet_id' => $this->spreadsheetId,
                'sheet_name' => $this->sheetName
            ]));
            
            // Check if it's a 404 error (spreadsheet not found)
            if (strpos($e->getMessage(), '404') !== false || strpos($e->getMessage(), 'not found') !== false) {
                throw new \Exception('الجدول غير موجود أو لا يمكن الوصول إليه. تحقق من Spreadsheet ID وصلاحيات Service Account.');
            }
            
            throw $e; // Re-throw to handle in the controller
        }
    }

    /**
     * Clear all data from the sheet (useful for maintenance)
     */
    public function clearAllData(): bool
    {
        try {
            $range = $this->sheetName . '!A:F';
            
            $this->service->spreadsheets_values->batchClear(
                $this->spreadsheetId,
                new \Google\Service\Sheets\BatchClearValuesRequest([
                    'ranges' => [$range]
                ])
            );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear all data from Google Sheet: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add statistics to the sheet
     */
    public function addStatistics(array $statistics): bool
    {
        try {
            $data = [
                date('Y-m-d H:i:s'),
                $statistics['total_users'] ?? 0,
                $statistics['active_users'] ?? 0,
                $statistics['total_videos'] ?? 0,
                $statistics['total_quizzes'] ?? 0,
                $statistics['total_attendance'] ?? 0,
                $statistics['total_recitations'] ?? 0,
            ];
            
            return $this->addRow($data);
        } catch (\Exception $e) {
            Log::error('Failed to add statistics to Google Sheets: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Collect statistics from the database
     */
    public function collectStatistics(): array
    {
        return [
            'total_users' => \App\Models\User::count(),
            //'active_users' => \App\Models\User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'active_users' => \App\Models\User::count(),
            'total_videos' => \App\Models\Video::count(),
            'total_quizzes' => \App\Models\Quiz::count(),
            'total_attendance' => \App\Models\Attendance::count(),
            'total_recitations' => \App\Models\RecitationRecord::count(),
        ];
    }
}
