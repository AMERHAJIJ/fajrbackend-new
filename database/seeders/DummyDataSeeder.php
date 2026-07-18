<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Blog;
use App\Models\Video;
use App\Models\RecitationRecord;
use App\Models\User;
use App\Models\Subject;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Homework;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. توليد 5 أخبار مدرسة
        $category = Category::firstOrCreate(['name' => 'أخبار عامة']);
        for ($i = 1; $i <= 5; $i++) {
            Blog::create([
                'title' => "خبر مدرسة الفجر رقم $i",
                'content' => "هذا نص تجريبي للخبر رقم $i يتحدث عن نشاطات مدرسة الفجر المتميزة وتفوق الطلاب في حفظ القرآن الكريم.",
                'category_id' => $category->id,
                'showInHomePage' => true,
                'active' => true,
            ]);
        }

        // 2. توليد 5 فيديوهات تعليمية
        $videos = [];
        for ($i = 1; $i <= 5; $i++) {
            $videos[] = Video::create([
                'name' => "درس تعليمي: تجويد القرآن - الجزء $i",
                'link' => "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
                'object_type' => \App\Models\Category::class,
                'object_id' => $category->id,
                'showInHomePage' => true,
                'active' => true,
            ]);
        }

        // 3. توليد 10 سجلات تسميع
        $students = User::role('student')->take(10)->get();
        $teacher = User::role('teacher')->first() ?? User::role('admin')->first();
        $subject = Subject::where('is_quran', true)->first() ?? Subject::first();

        if ($students->isNotEmpty() && $teacher && $subject) {
            foreach ($students as $index => $student) {
                $record = RecitationRecord::create([
                    'student_id' => $student->id,
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'date' => now()->subDays(rand(0, 5)),
                    'score' => rand(7, 10),
                ]);
                
                // إضافة سور عشوائية للسجل
                $surahId = rand(1, 114);
                $record->surahs()->attach($surahId, [
                    'type' => 'ayah',
                    'fromAyeh' => 1,
                    'toAyeh' => rand(10, 50),
                ]);
            }
        }

        // 4. توليد 3 اختبارات مع أسئلة وأجوبة
        for ($i = 1; $i <= 3; $i++) {
            $quiz = Quiz::create([
                'title' => "اختبار تجريبي في مادة القرآن - المستوى $i",
                'active' => true,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'video_id' => isset($videos[$i-1]) ? $videos[$i-1]->id : null,
            ]);

            for ($j = 1; $j <= 3; $j++) {
                $question = Question::create([
                    'name' => "السؤال رقم $j في الاختبار $i: ما هو حكم التجويد في هذه الآية؟",
                    'active' => true,
                    'quiz_id' => $quiz->id,
                ]);

                Answer::create(['title' => 'الإظهار', 'isCorrect' => $j == 1, 'question_id' => $question->id]);
                Answer::create(['title' => 'الإدغام', 'isCorrect' => $j == 2, 'question_id' => $question->id]);
                Answer::create(['title' => 'الإخفاء', 'isCorrect' => $j == 3, 'question_id' => $question->id]);
            }
        }

        // 5. توليد 3 واجبات قرآنية
        for ($i = 1; $i <= 3; $i++) {
            Homework::create([
                'title' => "واجب منزلي رقم $i",
                'description' => "يرجى مراجعة الآيات المطلوبة وحفظها جيداً للدرس القادم.",
                'lesson_name' => "درس سورة البقرة - المقطع $i",
                'page_number' => rand(1, 604),
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'due_date' => now()->addDays(rand(1, 7)),
                'max_score' => 10,
                'active' => true,
            ]);
        }

        // --- ربط الطالب التجريبي بالمادة المتاحة لكي تظهر له الواجبات ---
        $student = \App\Models\User::where('email', 'student@fajr.com')->first();
        $subject = \App\Models\Subject::first();
        
        if (!$subject) {
            $subject = \App\Models\Subject::create([
                'title' => 'القرآن الكريم',
                'active' => true
            ]);
        } else {
            $subject->update(['title' => 'القرآن الكريم']);
        }

        if ($student && $subject) {
            // تنظيف أي روابط قديمة لتجنب التكرار أو الأخطاء
            \DB::table('student_subjects')->where('student_id', $student->id)->delete();
            
            \DB::table('student_subjects')->insert([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'enrolled_at' => now(),
            ]);

            // التأكد من وجود فيديوهات لهذه المادة
            \App\Models\Video::where('object_type', 'subject')->where('object_id', $subject->id)->delete();
            
            \App\Models\Video::create([
                'name' => 'شرح مخارج الحروف (جديد)',
                'link' => 'https://www.youtube.com/watch?v=kYv_I-3N7fA',
                'object_type' => 'subject',
                'object_id' => $subject->id,
                'active' => true,
                'showInHomePage' => true
            ]);

            \App\Models\Video::create([
                'name' => 'تلاوة عطرة - سورة البقرة',
                'link' => 'https://www.youtube.com/watch?v=vV_X1Gz6Z0A',
                'object_type' => 'subject',
                'object_id' => $subject->id,
                'active' => true,
                'showInHomePage' => true
            ]);
        }
    }
}
