<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Video;
use App\Models\RecitationRecord;
use App\Models\NextRecitation;
use App\Models\Homework;
use App\Models\Attendance;
use App\Models\Quiz;
use App\Models\File;
use Illuminate\Http\Request;

// [شرح أكاديمي للمناقشة]:
// هذا المتحكم (Controller) هو القلب النابض الذي يربط بين (موقع Laravel) و(تطبيق Flutter).
// نستخدم هنا معمارية (RESTful APIs). التطبيق يطلب البيانات عبر روابط (Endpoints)،
// وهذا الملف يقوم بجمع البيانات من قاعدة البيانات وإعادتها بصيغة (JSON) 
// لكي يفهمها تطبيق الموبايل بسهولة ويعرضها للطالب بشكل جميل وسريع.
class DataController extends Controller
{
    /**
     * جلب الأخبار
     */
    public function getNews()
    {
        $news = Blog::where('active', true)->latest()->get();
        return response()->json($news);
    }

    /**
     * جلب الفيديوهات
     */
    public function getVideos(Request $request)
    {
        $user = $request->user();
        $subjectIds = $user->subjectsAsStudent()->pluck('subjects.id');

        $videos = Video::where('active', true)
            ->where(function($query) use ($subjectIds) {
                $query->where('object_type', 'App\Models\Category')
                      ->orWhere(function($q) use ($subjectIds) {
                          $q->where('object_type', 'App\Models\Subject')
                            ->whereIn('object_id', $subjectIds);
                      });
            })
            ->latest()
            ->get()
            ->map(function ($video) {
                // إضافة حقل title كنسخة من name لضمان ظهور الاسم في التطبيق
                $video->title = $video->name;
                
                if ($video->image) {
                    $video->image = url('storage/' . $video->image);
                }
                
                // جلب اسم المعلم من المادة المرتبطة
                if ($video->object_type === 'App\Models\Subject') {
                    $subject = \App\Models\Subject::with('teachers')->find($video->object_id);
                    $video->instructor = $subject->teachers->first()->name ?? 'مدرس المادة';
                } else {
                    $video->instructor = 'إدارة المركز';
                }
                
                return $video;
            });
            
        return response()->json($videos);
    }

    /**
     * جلب سجلات تسميع الطالب الحالي
     */
    public function getRecitations(Request $request)
    {
        $user = $request->user();
        $recitations = RecitationRecord::with(['surahs', 'student'])
            ->where('student_id', $user->id)
            ->latest()
            ->get();
            
        return response()->json($recitations);
    }

    /**
     * جلب التلاوات القادمة
     */
    public function getNextRecitations(Request $request)
    {
        $user = $request->user();
        $next = NextRecitation::with(['surahs'])
            ->where('student_id', $user->id)
            ->latest()
            ->get();
            
        return response()->json($next);
    }

    /**
     * جلب الواجبات
     */
    public function getHomeworks(Request $request)
    {
        $user = $request->user();
        $subjectIds = $user->subjectsAsStudent()->pluck('subjects.id');
        
        $homeworks = Homework::with(['subject', 'teacher:id,name'])
            ->whereIn('subject_id', $subjectIds)
            ->where('active', true)
            ->latest()
            ->get();
            
        return response()->json($homeworks);
    }

    /**
     * جلب سجل الحضور
     */
    public function getAttendance(Request $request)
    {
        $user = $request->user();
        $attendance = Attendance::where('student_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($record) {
                // التطبيق الموبايل يتوقع النص 'present' أو 'absent' بدلاً من 1 أو 0
                $record->status = $record->status ? 'present' : 'absent';
                return $record;
            });
            
        return response()->json($attendance);
    }

    /**
     * جلب الاختبارات المتاحة
     */
    public function getQuizzes(Request $request)
    {
        $user = $request->user();
        $subjectIds = $user->subjectsAsStudent()->pluck('subjects.id');

        $quizzes = Quiz::with(['questions.answers'])
            ->whereIn('subject_id', $subjectIds)
            ->where('active', true)
            ->get();
            
        return response()->json($quizzes);
    }

    /**
     * جلب الملفات والمناهج
     */
    public function getFiles(Request $request)
    {
        $user = $request->user();
        $subjectIds = $user->subjectsAsStudent()->pluck('subjects.id');

        $files = File::where('active', true)
            ->where(function($query) use ($subjectIds) {
                // ملفات عامة أو ملفات مرتبطة بمواد الطالب
                $query->where('is_public', true)
                      ->orWhere(function($q) use ($subjectIds) {
                          $q->where('object_type', 'App\\Models\\Subject')
                            ->whereIn('object_id', $subjectIds);
                      });
            })
            ->latest()
            ->get()
            ->map(function ($file) {
                if ($file->image) {
                    $file->image = url('storage/' . $file->image);
                }
                if ($file->link) {
                    $file->link = url('/') . '/storage/' . $file->link;
                    $file->file_url = $file->link;
                    $file->file_path = $file->link;
                    $file->download_url = $file->link;
                }
                return $file;
            });
            
        return response()->json($files);
    }

    /**
     * جلب الإشعارات
     */
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->get();
        return response()->json($notifications);
    }

    /**
     * تحديد الإشعارات كمقروءة
     */
    public function markNotificationsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['message' => 'تم تحديد الكل كمقروء']);
    }
}
