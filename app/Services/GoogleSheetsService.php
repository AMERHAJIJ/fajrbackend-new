<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;
use Google\Service\Sheets\ValueRange;

// [شرح أكاديمي للمناقشة]:
// الهدف من هذه الخدمة هو ميزة (Automated Reporting الأتمتة الإدارية).
// بدلاً من أن تدخل الإدارة إلى لوحة التحكم يومياً لنسخ إحصائيات الطلاب والمبيعات،
// يقوم السيرفر عبر هذه الخدمة بتصدير البيانات فوراً وبشكل آلي إلى جداول Google Sheets.
// هذا يتيح للإدارة مشاركة الإحصائيات مع جهات أخرى (غير تقنية) بصيغة إكسيل مألوفة وسهلة.
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
            
            // Try to auto-create file from base64 if env is set
            $base64Json = env('GOOGLE_SERVICE_ACCOUNT_JSON_BASE64');
            if ($base64Json) {
                $decoded = base64_decode(str_replace(["\r", "\n", ' '], '', $base64Json));
                if ($decoded) {
                    // Ensure directory exists
                    if (!is_dir(dirname($serviceAccountPath))) {
                        mkdir(dirname($serviceAccountPath), 0755, true);
                    }
                    file_put_contents($serviceAccountPath, $decoded);
                }
            }
            
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
            // Log::error('Service Account Path: ' . $serviceAccountPath ?? 'null');
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

    private static function getArabicDayName(\Carbon\Carbon $date): string
    {
        $dayNames = [
            'Sunday' => 'الأحد',
            'Monday' => 'الإثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
        ];
        return $dayNames[$date->format('l')] ?? '';
    }

    public function updateStudentReport(int $subjectId, string $date, string $reportType = 'daily'): bool
    {
        try {
            // Test connection first
            if (!$this->testConnection()) {
                throw new \Exception('لا يمكن الاتصال بـ Google Sheets. تحقق من Spreadsheet ID وصلاحيات Service Account.');
            }

            $subject = \App\Models\Subject::find($subjectId);
            if (!$subject) {
                throw new \Exception('الحلقة غير موجودة في قاعدة البيانات.');
            }

            // 1. Determine sheet name from date
            $carbonDate = \Carbon\Carbon::parse($date);
            $dayName = self::getArabicDayName($carbonDate);
            $formattedDate = $carbonDate->format('d/m/Y');
            $sheetName = "{$dayName}: {$formattedDate}";

            // Check if sheet exists
            try {
                $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
                $sheets = $spreadsheet->getSheets();
                $sheetExists = false;
                foreach ($sheets as $s) {
                    if ($s->getProperties()->getTitle() === $sheetName) {
                        $sheetExists = true;
                        break;
                    }
                }
                if (!$sheetExists) {
                    throw new \Exception("لم يتم العثور على ورقة عمل باسم '{$sheetName}' في الملف. يرجى إنشائها أولاً.");
                }
            } catch (\Exception $e) {
                Log::error('Google Sheets metadata error: ' . $e->getMessage());
                throw new \Exception("فشل الوصول إلى ملف جداول البيانات: " . $e->getMessage());
            }

            // 2. Map subject to vertical/horizontal coordinates
            $subjectTitle = $subject->title;
            $titleClean = mb_strtolower($subjectTitle);

            // Determine vertical range
            $headerRowNumber = 5;
            $startRow = 6;
            $endRow = 25;

            if (str_contains($titleClean, 'يافع') || str_contains($titleClean, 'يافعين')) {
                $headerRowNumber = 32;
                $startRow = 33;
                $endRow = 52;
            } elseif (str_contains($titleClean, 'فتيان') || str_contains($titleClean, 'فتية')) {
                $headerRowNumber = 59;
                $startRow = 60;
                $endRow = 79;
            }

            // Determine horizontal starting column
            $startCol = null;
            if (str_contains($titleClean, 'الأولى') || str_contains($titleClean, 'الاولى')) {
                $startCol = 2; // Column B (Index 2 in 1-based)
            } elseif (str_contains($titleClean, 'الثانية') || str_contains($titleClean, 'الثانيه')) {
                $startCol = 19; // Column S (Index 19 in 1-based)
            } elseif (str_contains($titleClean, 'الثالثة') || str_contains($titleClean, 'الثالثه')) {
                if (str_contains($titleClean, 'ب')) {
                    $startCol = 53; // Column BA (Index 53 in 1-based)
                } else {
                    $startCol = 36; // Column AJ (Index 36 in 1-based)
                }
            } elseif (str_contains($titleClean, 'الرابعة') || str_contains($titleClean, 'الرابعه')) {
                $startCol = 70; // Column BR (Index 70 in 1-based)
            }

            if ($startCol === null) {
                throw new \Exception("لا يمكن مطابقة الحلقة '{$subjectTitle}' مع أعمدة الجدول في ملف Google Sheets.");
            }

            // 3. Read Header Row dynamically
            $rangeHeaders = $sheetName . "!" . $this->numberToColumnLetter($startCol + 1) . "{$headerRowNumber}:" . $this->numberToColumnLetter($startCol + 17) . "{$headerRowNumber}";
            $responseHeaders = $this->service->spreadsheets_values->get($this->spreadsheetId, $rangeHeaders);
            $headers = $responseHeaders->getValues()[0] ?? [];

            $scoreColIndex = null;
            $pagesColIndex = null;
            $attendanceColIndex = null;
            $teacherColIndex = null;
            $surahsColIndex = null;
            $nameColIndex = $startCol + 1; // Column C / T / AK / BB / BS
            $familyColIndex = $startCol + 2; // Column D / U / AL / BC / BT
            $ageColIndex = $startCol + 3; // Column E / V / AM / BD / BU
            $regColIndex = $startCol + 4; // Column F / W / AN / BE / BV

            foreach ($headers as $colOffset => $headerText) {
                $colIdx = $startCol + 1 + $colOffset;
                if (str_contains($headerText, 'علامة الحفظ')) {
                    $scoreColIndex = $colIdx;
                } elseif (str_contains($headerText, 'الحضور')) {
                    $attendanceColIndex = $colIdx;
                } elseif (str_contains($headerText, 'الأستاذ')) {
                    $teacherColIndex = $colIdx;
                } elseif (str_contains($headerText, 'المحصلات')) {
                    $surahsColIndex = $colIdx;
                } elseif (str_contains($headerText, 'عدد الصفحات') && $colIdx < $startCol + 10) {
                    $pagesColIndex = $colIdx;
                }
            }

            if (!$scoreColIndex || !$attendanceColIndex) {
                throw new \Exception("فشل قراءة العناوين في ورقة العمل. يرجى التأكد من تطابق ترويسة الجدول مع القالب المعتمد.");
            }

            // 4. Read existing names in C and D for rows $startRow to $endRow
            $rangeNames = $sheetName . "!" . $this->numberToColumnLetter($nameColIndex) . "{$startRow}:" . $this->numberToColumnLetter($familyColIndex) . "{$endRow}";
            $responseNames = $this->service->spreadsheets_values->get($this->spreadsheetId, $rangeNames);
            $existingRows = $responseNames->getValues() ?: [];

            Log::info('Debug Sheets Sync:', [
                'rangeNames' => $rangeNames,
                'existingRows_count' => count($existingRows),
                'existingRows' => $existingRows,
                'startRow' => $startRow,
                'endRow' => $endRow,
                'startCol' => $startCol
            ]);

            // Fetch students from Database sorted alphabetically
            $students = \App\Models\User::role('student')
                ->whereHas('subjectsAsStudent', function($query) use ($subjectId) {
                    $query->where('subject_id', $subjectId);
                })
                ->orderBy('name', 'asc')
                ->get();

            $updateRequests = [];

            foreach ($students as $student) {
                // Split full name
                $parts = explode(' ', trim($student->name));
                $firstName = $parts[0] ?? '';
                $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
                $age = $student->birthday ? \Carbon\Carbon::parse($student->birthday)->age : 'غير محدد';

                $shouldWriteProfile = false;

                // Find matched row
                $matchedRow = null;
                foreach ($existingRows as $index => $rowVal) {
                    $rowNum = $index + $startRow;
                    $sheetNameCell = trim($rowVal[0] ?? '');
                    $sheetFamilyCell = trim($rowVal[1] ?? '');
                    $sheetFullName = trim($sheetNameCell . ' ' . $sheetFamilyCell);

                    if (mb_strtolower($sheetFullName) === mb_strtolower(trim($student->name))) {
                        $matchedRow = $rowNum;
                        break;
                    }
                }

                // If not found, find first empty row
                if (!$matchedRow) {
                    for ($i = 0; $i < ($endRow - $startRow + 1); $i++) {
                        $rowNum = $i + $startRow;
                        $sheetNameCell = trim($existingRows[$i][0] ?? '');
                        $sheetFamilyCell = trim($existingRows[$i][1] ?? '');
                        if ($sheetNameCell === '' && $sheetFamilyCell === '') {
                            $matchedRow = $rowNum;
                            $shouldWriteProfile = true;
                            // Update local copy so next student doesn't take same row
                            $existingRows[$i][0] = $firstName;
                            $existingRows[$i][1] = $lastName;
                            break;
                        }
                    }
                }

                if (!$matchedRow) {
                    Log::warning("No available rows in Google Sheet for student: {$student->name}");
                    continue;
                }

                // Gather stats for student today
                $attendance = \App\Models\Attendance::where('student_id', $student->id)
                    ->where('subject_id', $subjectId)
                    ->whereDate('date', $date)
                    ->first();

                $attendanceStatus = 'غائب';
                if ($attendance) {
                    $attendanceStatus = $attendance->status ? 'حاضر' : 'غائب';
                }

                $recitationRecord = \App\Models\RecitationRecord::where('student_id', $student->id)
                    ->where('subject_id', $subjectId)
                    ->whereDate('date', $date)
                    ->with('surahs')
                    ->first();

                $recitationScore = '';
                $pagesCount = '';
                $surahNames = '';
                $teacherName = $subject->teachers->first()->name ?? 'غير محدد';

                if ($recitationRecord) {
                    $recitationScore = $recitationRecord->score;

                    $totalPages = 0;
                    $surahsList = [];
                    foreach ($recitationRecord->surahs as $surah) {
                        $surahsList[] = $surah->name;
                        if ($surah->pivot->type === 'page') {
                            $totalPages += max(0, $surah->pivot->toPage - $surah->pivot->fromPage + 1);
                        } else {
                            if ($surah->pivot->fromPage && $surah->pivot->toPage) {
                                $totalPages += max(0, $surah->pivot->toPage - $surah->pivot->fromPage + 1);
                            }
                        }
                    }
                    $pagesCount = $totalPages > 0 ? $totalPages : '';
                    $surahNames = implode('، ', $surahsList);

                    if ($recitationRecord->teacher) {
                        $teacherName = $recitationRecord->teacher->name;
                    }
                }

                // If name is empty in sheet, write profile
                if ($shouldWriteProfile) {
                    $updateRequests[] = new \Google\Service\Sheets\ValueRange([
                        'range' => "{$sheetName}!" . $this->numberToColumnLetter($nameColIndex) . "{$matchedRow}:" . $this->numberToColumnLetter($regColIndex) . "{$matchedRow}",
                        'values' => [[$firstName, $lastName, $age, 'مسجل']]
                    ]);
                }

                // Write Recitation Score and Pages
                $updateRequests[] = new \Google\Service\Sheets\ValueRange([
                    'range' => "{$sheetName}!" . $this->numberToColumnLetter($scoreColIndex) . "{$matchedRow}:" . $this->numberToColumnLetter($pagesColIndex) . "{$matchedRow}",
                    'values' => [[$recitationScore, $pagesCount]]
                ]);

                // Write Attendance
                $updateRequests[] = new \Google\Service\Sheets\ValueRange([
                    'range' => "{$sheetName}!" . $this->numberToColumnLetter($attendanceColIndex) . "{$matchedRow}",
                    'values' => [[$attendanceStatus]]
                ]);

                // Write Teacher
                if ($teacherColIndex) {
                    $updateRequests[] = new \Google\Service\Sheets\ValueRange([
                        'range' => "{$sheetName}!" . $this->numberToColumnLetter($teacherColIndex) . "{$matchedRow}",
                        'values' => [[$teacherName]]
                    ]);
                }

                // Write Surahs (المحصلات)
                if ($surahsColIndex) {
                    $updateRequests[] = new \Google\Service\Sheets\ValueRange([
                        'range' => "{$sheetName}!" . $this->numberToColumnLetter($surahsColIndex) . "{$matchedRow}",
                        'values' => [[$surahNames]]
                    ]);
                }
            }

            if (!empty($updateRequests)) {
                $batchRequest = new \Google\Service\Sheets\BatchUpdateValuesRequest([
                    'valueInputOption' => 'USER_ENTERED',
                    'data' => $updateRequests
                ]);
                $this->service->spreadsheets_values->batchUpdate($this->spreadsheetId, $batchRequest);
                Log::info('Google Sheets batch update complete for subject: ' . $subjectId);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update student report in Google Sheets: ' . $e->getMessage());
            throw $e;
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
