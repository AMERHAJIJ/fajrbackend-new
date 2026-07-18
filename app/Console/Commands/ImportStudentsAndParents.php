<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportStudentsAndParents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-students-and-parents {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import students and parents from the summer club Excel registration sheet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file') ?? "C:\\Users\\ENG AMER\\Downloads\\النادي الصيفي 2026 (الردود) سجل الطلاب.xlsx";

        if (!file_exists($filePath)) {
            $this->error("Error: File not found at {$filePath}");
            return 1;
        }

        $this->info("Loading Excel file: {$filePath}...");

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            $this->error("Failed to load Excel file: " . $e->getMessage());
            return 1;
        }

        $totalRows = count($rows);
        $this->info("Found {$totalRows} rows including headers. Starting import...");

        // Ensure roles exist
        $parentRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $studentRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $importedParentsCount = 0;
        $importedStudentsCount = 0;

        // Skip headers (Row 1)
        for ($i = 2; $i <= $totalRows; $i++) {
            $row = $rows[$i];

            // If first name is empty, skip
            if (empty($row['B'])) {
                continue;
            }

            // 1. Process Parent
            $fatherPhoneRaw = $row['I'] ?? '';
            $fatherPhone = preg_replace('/[^0-9]/', '', $fatherPhoneRaw);

            if (empty($fatherPhone)) {
                $this->warn("Skipping Row {$i}: Father phone number is missing.");
                continue;
            }

            // Look for existing parent by phone
            $parent = \App\Models\User::where('phone', $fatherPhone)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'parent');
                })->first();

            if (!$parent) {
                // Generate unique email and username
                $parentUsername = 'parent_' . $fatherPhone;
                $parentEmail = $parentUsername . '@alfajr.tech';

                // In case parent email already exists for another role, make it unique
                $counter = 1;
                while (\App\Models\User::where('email', $parentEmail)->orWhere('username', $parentUsername)->exists()) {
                    $parentUsername = 'parent_' . $fatherPhone . '_' . $counter;
                    $parentEmail = 'parent_' . $fatherPhone . '_' . $counter . '@alfajr.tech';
                    $counter++;
                }

                $fatherName = trim($row['H'] ?? '');
                if (empty($fatherName)) {
                    $fatherName = 'ولي أمر الطالب ' . trim($row['B']);
                }

                // If father name has no family name, append child's family name
                $familyName = trim($row['C'] ?? '');
                if (!empty($familyName) && !str_contains($fatherName, $familyName)) {
                    $fatherName .= ' ' . $familyName;
                }

                $parent = \App\Models\User::create([
                    'name' => $fatherName,
                    'username' => $parentUsername,
                    'email' => $parentEmail,
                    'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                    'phone' => $fatherPhone,
                    'address' => $row['M'] ?? 'عنوان افتراضي',
                    'birthday' => '1980-01-01', // Default for parent
                    'active' => true,
                    'father_job' => $row['J'] ?? null,
                    'mother_phone' => preg_replace('/[^0-9]/', '', $row['K'] ?? null),
                ]);

                $parent->assignRole($parentRole);
                $importedParentsCount++;
            }

            // 2. Process Student
            $studentFirstName = trim($row['B'] ?? '');
            $studentLastName = trim($row['C'] ?? '');
            $studentFullName = $studentFirstName . ' ' . $studentLastName;

            // Generate unique username for student
            $asciiName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', \Str::ascii($row['D'] ?? $studentFirstName)));
            if (empty($asciiName)) {
                $asciiName = 'student';
            }
            $baseStudentUsername = 'student_' . $asciiName;
            $studentUsername = $baseStudentUsername;
            $counter = 1;
            while (\App\Models\User::where('username', $studentUsername)->exists()) {
                $studentUsername = $baseStudentUsername . '_' . $counter;
                $counter++;
            }
            $studentEmail = $studentUsername . '@alfajr.tech';

            // Parse birthday
            $birthdayStr = $row['E'] ?? '';
            $birthday = null;
            if (!empty($birthdayStr)) {
                if (is_numeric($birthdayStr)) {
                    $birthday = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($birthdayStr)->format('Y-m-d');
                } else {
                    $parsedTime = strtotime($birthdayStr);
                    $birthday = $parsedTime ? date('Y-m-d', $parsedTime) : '2015-01-01';
                }
            } else {
                $birthday = '2015-01-01'; // Default backup
            }

            // Classify age group
            $ageGroupText = $row['Z'] ?? '';
            $ageGroup = 'nashieen';
            if (str_contains($ageGroupText, 'ناشئ') || str_contains($ageGroupText, 'الناشئين')) {
                $ageGroup = 'nashieen';
            } elseif (str_contains($ageGroupText, 'يافع') || str_contains($ageGroupText, 'اليافعين')) {
                $ageGroup = 'yafeen';
            } elseif (str_contains($ageGroupText, 'فتيان') || str_contains($ageGroupText, 'الفتيان') || str_contains($ageGroupText, 'شباب') || str_contains($ageGroupText, 'الشباب')) {
                $ageGroup = 'fityan';
            } else {
                // Check age if birthday parsed
                if ($birthday) {
                    $age = \Carbon\Carbon::parse($birthday)->age;
                    if ($age >= 15) {
                        $ageGroup = 'fityan';
                    } elseif ($age >= 11) {
                        $ageGroup = 'yafeen';
                    } else {
                        $ageGroup = 'nashieen';
                    }
                }
            }

            // Wants bus
            $wantsBusTextChildren = $row['AA'] ?? '';
            $wantsBusTextTeens = $row['AK'] ?? '';
            $wantsBus = (str_contains($wantsBusTextChildren, 'نعم') || str_contains($wantsBusTextTeens, 'نعم'));

            // Medical notes
            $healthNotes = trim($row['X'] ?? '');
            $healthExplanation = trim($row['Y'] ?? '');
            $medicalNotes = null;
            if ($healthNotes && $healthNotes !== 'لا توجد ملاحظات صحية' && $healthNotes !== 'nan') {
                $medicalNotes = $healthNotes;
                if ($healthExplanation && $healthExplanation !== 'nan') {
                    $medicalNotes .= ' - توضيح: ' . $healthExplanation;
                }
            }

            // General notes
            $additionalNotes = trim($row['AJ'] ?? '');
            $generalNotes = null;
            if ($additionalNotes && $additionalNotes !== 'nan') {
                $generalNotes = $additionalNotes;
            }

            // Quran pages
            $quranPagesRaw = trim($row['O'] ?? '');
            $quranPages = 0;
            if (!empty($quranPagesRaw) && $quranPagesRaw !== 'nan') {
                $quranPages = (int) preg_replace('/[^0-9]/', '', $quranPagesRaw);
            }

            // Check if student with same name and parent already exists
            $student = \App\Models\User::where('name', $studentFullName)
                ->where('parent_id', $parent->id)
                ->first();

            if (!$student) {
                $student = \App\Models\User::create([
                    'name' => $studentFullName,
                    'username' => $studentUsername,
                    'email' => $studentEmail,
                    'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                    'phone' => $fatherPhone, // Uses father's phone for SMS/contact
                    'address' => $row['M'] ?? 'عنوان افتراضي',
                    'birthday' => $birthday,
                    'active' => true,
                    'parent_id' => $parent->id,
                    'school' => $row['G'] ?? null,
                    'father_job' => $row['J'] ?? null,
                    'mother_phone' => preg_replace('/[^0-9]/', '', $row['K'] ?? null),
                    'father_phone' => $fatherPhone,
                    'mother_job' => $row['L'] ?? null,
                    'age_group' => $ageGroup,
                    'medical_notes' => $medicalNotes,
                    'general_notes' => $generalNotes,
                    'wants_bus' => $wantsBus,
                    'quran_pages' => $quranPages,
                ]);

                $student->assignRole($studentRole);
                $importedStudentsCount++;
            } else {
                // Update new fields if student already exists
                $student->update([
                    'father_phone' => $fatherPhone,
                    'mother_job' => $row['L'] ?? null,
                    'school' => $row['G'] ?? null,
                    'father_job' => $row['J'] ?? null,
                    'mother_phone' => preg_replace('/[^0-9]/', '', $row['K'] ?? null),
                    'age_group' => $ageGroup,
                    'medical_notes' => $medicalNotes,
                    'general_notes' => $generalNotes,
                    'wants_bus' => $wantsBus,
                    'quran_pages' => $quranPages,
                ]);
                $importedStudentsCount++;
            }
        }

        $this->info("Import completed successfully!");
        $this->info("Parents created/found: {$importedParentsCount}");
        $this->info("Students imported: {$importedStudentsCount}");
        return 0;
    }
}
