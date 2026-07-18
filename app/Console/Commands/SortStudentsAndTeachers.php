<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SortStudentsAndTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sort-students-and-teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sort students into subjects (circles) and assign teachers based on PDF schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonPath = "C:\\Users\\ENG AMER\\.gemini\antigravity\\brain\\1dfd95c8-3bb3-4e0e-acc8-30c47e92e5da\\scratch\\students_pdf_data_new.json";

        if (!file_exists($jsonPath)) {
            $this->error("JSON data file not found at: {$jsonPath}");
            return 1;
        }

        $pdfData = json_decode(file_get_contents($jsonPath), true);
        $dbStudents = \App\Models\User::role('student')->get();

        $this->info("Starting student and teacher sorting process...");

        // Ensure teacher role exists
        $teacherRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);

        // Manual student name overrides for fuzzy matching
        $manualOverrides = [
            'حس دعبول' => 'حسين دعبول',
            'أحمد الع' => 'أحمد العجي',
            'محمد الع' => 'محمد العجي',
            'عبد الوهاب الع' => 'عبد الوهاب العجي',
            'أم الشعار' => 'امير الشعار',
            'يح چل' => 'يحيى جلبي',
            'محمود شب' => 'محمود شبلي',
            'مصط وىل' => 'مصطفي ولي',
            'ب جنيدي' => 'بشر الجنيدي',
            'عبد اللطيف عبد الموىل' => 'عبد اللطيف عبد المولى',
            'وسام قضما' => 'وسام القضماني',
            'حمزة شعار' => 'حمزه الشعار',
            'حمزة المرصي' => 'حمزة المصري',
            'عبد الله المرصي' => 'عبدالله المصري',
            'عبد الهادي المرصي' => 'عبد الهادي المصري',
            'ق سكري' => 'قصي سكري'
        ];

        // Define subject teachers map based on schedule images
        $subjectTeachersMap = [
            'الناشئة الأولى' => [
                'المجلس العلمي' => ['حسن سبسي', 'ياسين يلدز'],
                'المجلس التربوي' => ['ياسين يلدز', 'حسن سبسي'],
                'اللغة العربية' => ['حسن سبسي', 'ياسين يلدز']
            ],
            'الناشئة الثانية' => [
                'المجلس العلمي' => ['إبراهيم قدح', 'إبراهيم أبو داوود'],
                'المجلس التربوي' => ['أسامة الصوفي', 'إبراهيم أبو داوود'],
                'اللغة العربية' => ['أنس مصطفى']
            ],
            'الناشئة الثالثة' => [
                'المجلس العلمي' => ['أسامة الصوفي', 'عبد الرحمن حداد'],
                'المجلس التربوي' => ['أسامة الصوفي', 'أحمد خباز'],
                'اللغة العربية' => ['أنس مصطفى']
            ],
            'اليافعين الأولى' => [
                'المجلس العلمي' => ['أحمد خباز', 'عبد الرحمن حداد'],
                'المجلس التربوي' => ['عامر حجيج', 'عبد الرحمن حداد'],
                'اللغة العربية' => ['عدنان أبو شعر', 'عبد الرحمن حداد']
            ],
            'اليافعين الثانية' => [
                'المجلس العلمي' => ['أسامة الصوفي', 'أحمد خباز'],
                'المجلس التربوي' => ['أحمد خباز', 'حسن سبسي'],
                'اللغة العربية' => ['عدنان أبو شعر', 'أنس مصطفى']
            ],
            'اليافعين الثالثة' => [
                'المجلس العلمي' => [],
                'المجلس التربوي' => [],
                'اللغة العربية' => []
            ],
        ];

        // Define Quran subjects teachers mapping
        $quranSubjectTeachersMap = [
            'الناشئة الأولى' => ['حسن سبسي', 'ياسين يلدز'],
            'الناشئة الثانية' => ['إبراهيم قدح', 'إبراهيم أبو داوود', 'أسامة الصوفي', 'أنس مصطفى'],
            'الناشئة الثالثة' => ['أسامة الصوفي', 'عبد الرحمن حداد', 'أحمد خباز', 'أنس مصطفى'],
            'اليافعين الأولى' => ['أحمد خباز', 'عبد الرحمن حداد', 'عامر حجيج', 'عدنان أبو شعر'],
            'اليافعين الثانية' => ['أسامة الصوفي', 'أحمد خباز', 'حسن سبسي', 'عدنان أبو شعر', 'أنس مصطفى'],
            'اليافعين الثالثة' => [],
        ];

        // 1. Create Teacher accounts first
        $teacherUsers = [];
        $allTeacherNames = [];
        foreach ($subjectTeachersMap as $groupName => $subjects) {
            foreach ($subjects as $subjectType => $teachers) {
                foreach ($teachers as $teacherName) {
                    $allTeacherNames[$teacherName] = true;
                }
            }
        }

        foreach (array_keys($allTeacherNames) as $teacherName) {
            // Check if teacher already exists
            $teacher = \App\Models\User::where('name', $teacherName)
                ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
                ->first();

            if (!$teacher) {
                // Generate username and email
                $asciiName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', \Str::ascii($teacherName)));
                if (empty($asciiName)) {
                    $asciiName = 'teacher';
                }
                $baseUsername = 'teacher_' . $asciiName;
                $username = $baseUsername;
                $counter = 1;
                while (\App\Models\User::where('username', $username)->exists()) {
                    $username = $baseUsername . '_' . $counter;
                    $counter++;
                }
                $email = $username . '@alfajr.tech';

                $teacher = \App\Models\User::create([
                    'name' => $teacherName,
                    'username' => $username,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                    'phone' => '0555' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT), // Dummy phone to satisfy database non-null constraint
                    'address' => 'عنوان افتراضي',
                    'birthday' => '1985-01-01',
                    'active' => true,
                ]);
                $teacher->assignRole($teacherRole);
                $this->info("Created Teacher account: {$teacherName} ({$username})");
            }
            $teacherUsers[$teacherName] = $teacher;
        }

        // Helper functions for matching
        $cleanArabicName = function ($name) {
            $name = str_replace(['أ', 'إ', 'آ'], 'ا', $name);
            $name = str_replace('ة', 'ه', $name);
            $name = str_replace('ى', 'ي', $name);
            $name = str_replace(' ', '', $name);
            return preg_replace('/[^ا-ي]/u', '', $name);
        };

        $findMatch = function ($pdfName) use ($dbStudents, $cleanArabicName, $manualOverrides) {
            // Check manual overrides first
            if (isset($manualOverrides[$pdfName])) {
                $overrideName = $manualOverrides[$pdfName];
                $matched = $dbStudents->first(fn ($s) => $s->name === $overrideName);
                if ($matched) return $matched;
            }

            $cleanPdf = $cleanArabicName($pdfName);
            $bestMatch = null;
            $bestScore = 0;

            foreach ($dbStudents as $dbStudent) {
                $cleanDb = $cleanArabicName($dbStudent->name);

                if (str_starts_with($cleanDb, $cleanPdf) || str_starts_with($cleanPdf, $cleanDb)) {
                    return $dbStudent;
                }

                similar_text($cleanPdf, $cleanDb, $percent);
                if ($percent > $bestScore) {
                    $bestScore = $percent;
                    $bestMatch = $dbStudent;
                }
            }

            return ($bestScore >= 85) ? $bestMatch : null;
        };

        // 2. Process groups (Circles) and Subjects
        foreach (['yafeen' => 'yafeen', 'nashieen' => 'nashieen'] as $catKey => $ageGroupVal) {
            if (!isset($pdfData[$catKey])) continue;

            foreach ($pdfData[$catKey] as $group) {
                $groupName = $group['name'];
                $studentsList = $group['students'];

                $this->info("\nProcessing Circle: {$groupName}");

                // Validate that we have mapping for this group
                if (!isset($subjectTeachersMap[$groupName])) {
                    $this->warn("Skipping subject mapping for: {$groupName} (No teachers mapped in schedule)");
                    continue;
                }

                // Create the 3 academic subjects and link teachers
                $subjectModels = [];
                foreach (['المجلس العلمي', 'المجلس التربوي', 'اللغة العربية'] as $subjectType) {
                    $subjectTitle = "{$groupName} - {$subjectType}";
                    
                    $subject = \App\Models\Subject::firstOrCreate(
                        ['title' => $subjectTitle],
                        ['active' => true, 'is_quran' => false]
                    );
                    $subjectModels[$subjectType] = $subject;

                    // Sync teachers
                    $assignedTeacherNames = $subjectTeachersMap[$groupName][$subjectType] ?? [];
                    $assignedTeacherIds = [];
                    foreach ($assignedTeacherNames as $tName) {
                        if (isset($teacherUsers[$tName])) {
                            $assignedTeacherIds[] = $teacherUsers[$tName]->id;
                        }
                    }
                    $subject->teachers()->sync($assignedTeacherIds);
                }

                // Create the Quran subject (is_quran = true)
                $quranSubjectType = ($ageGroupVal === 'nashieen') ? 'سورة نوح' : 'سورة يوسف';
                $quranSubjectTitle = "{$groupName} - {$quranSubjectType}";
                $quranSubject = \App\Models\Subject::firstOrCreate(
                    ['title' => $quranSubjectTitle],
                    ['active' => true, 'is_quran' => true]
                );

                // Sync teachers to Quran subject
                $quranTeacherNames = $quranSubjectTeachersMap[$groupName] ?? [];
                $quranTeacherIds = [];
                foreach ($quranTeacherNames as $tName) {
                    if (isset($teacherUsers[$tName])) {
                        $quranTeacherIds[] = $teacherUsers[$tName]->id;
                    }
                }
                $quranSubject->teachers()->sync($quranTeacherIds);

                // Match and assign students
                $assignedCount = 0;
                foreach ($studentsList as $pdfStudentName) {
                    if ($pdfStudentName === 'اسم الطالب') {
                        continue;
                    }
                    $student = $findMatch($pdfStudentName);

                    if ($student) {
                        // Update age group
                        $student->update(['age_group' => $ageGroupVal]);

                        // Sync all 4 subjects (3 academic + 1 Quran)
                        $subjectIds = collect($subjectModels)->pluck('id')
                            ->push($quranSubject->id)
                            ->toArray();
                            
                        $student->subjectsAsStudent()->syncWithPivotValues($subjectIds, []);
                        $assignedCount++;
                    } else {
                        $this->warn("  Warning: Student '{$pdfStudentName}' in PDF was not matched in DB.");
                    }
                }
                $this->info("  Assigned {$assignedCount} / " . count($studentsList) . " students to subjects.");
            }
        }

        $this->info("\nSorting and assignment process completed successfully!");
        return 0;
    }
}
