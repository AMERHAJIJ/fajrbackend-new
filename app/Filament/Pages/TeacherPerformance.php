<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Subject;
use App\Models\TeacherTaskTracker;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TeacherPerformance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    public static function getNavigationLabel(): string { return 'لوحة تقييم المعلمين'; }
    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable { return 'لوحة تقييم المعلمين والأداء'; }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.reports_statistics'); }
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.teacher-performance';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function getTeacherStats(): array
    {
        $teachers = User::role('teacher')->with('subjectsAsTeacher')->get();
        $stats = [];

        foreach ($teachers as $teacher) {
            $subjects = $teacher->subjectsAsTeacher;
            $subjectIds = $subjects->pluck('id')->toArray();
            
            // Total unique students enrolled in this teacher's subjects
            $studentsCount = User::role('student')
                ->whereHas('subjectsAsStudent', function($q) use ($subjectIds) {
                    $q->whereIn('subject_id', $subjectIds);
                })->count();

            // Fetch all trackers for this teacher
            $trackers = TeacherTaskTracker::where('teacher_id', $teacher->id)->get();
            $totalDays = $trackers->count();

            $attendanceScore = 0;
            $recitationScore = 0;
            $nextRecitationScore = 0;
            $homeworkScore = 0;
            $whatsappScore = 0;

            if ($totalDays > 0) {
                $attendanceSum = 0;
                $recitationSum = 0;
                $nextRecitationSum = 0;
                $homeworkSum = 0;
                $whatsappSum = 0;

                foreach ($trackers as $tracker) {
                    $attendanceSum += $tracker->getAttendanceStats()['percentage'];
                    $recitationSum += $tracker->getRecitationStats()['percentage'];
                    $nextRecitationSum += $tracker->getNextRecitationStats()['percentage'];
                    $homeworkSum += $tracker->getHomeworkStats()['percentage'];
                    $whatsappSum += $tracker->getWhatsappStats()['percentage'];
                }

                $attendanceScore = round($attendanceSum / $totalDays);
                $recitationScore = round($recitationSum / $totalDays);
                $nextRecitationScore = round($nextRecitationSum / $totalDays);
                $homeworkScore = round($homeworkSum / $totalDays);
                $whatsappScore = round($whatsappSum / $totalDays);
            }

            $overallScore = round(($attendanceScore + $recitationScore + $nextRecitationScore + $homeworkScore + $whatsappScore) / 5);

            // Determine badge
            if ($overallScore >= 90) {
                $badgeName = 'وسام التميز الذهبي';
                $badgeIcon = '🥇';
                $badgeColor = 'text-yellow-500 bg-yellow-50 border-yellow-200';
            } elseif ($overallScore >= 75) {
                $badgeName = 'وسام التميز الفضي';
                $badgeIcon = '🥈';
                $badgeColor = 'text-gray-500 bg-gray-50 border-gray-200';
            } elseif ($overallScore >= 50) {
                $badgeName = 'وسام التميز البرونزي';
                $badgeIcon = '🥉';
                $badgeColor = 'text-amber-600 bg-amber-50 border-amber-200';
            } else {
                $badgeName = 'يحتاج إلى تحسين';
                $badgeIcon = '🛑';
                $badgeColor = 'text-red-500 bg-red-50 border-red-200';
            }

            $stats[] = [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'subjects' => $subjects->pluck('title')->implode('، ') ?: 'لا يوجد حلقات مسندة',
                'students_count' => $studentsCount,
                'total_days' => $totalDays,
                'attendance_score' => $attendanceScore,
                'recitation_score' => $recitationScore,
                'next_recitation_score' => $nextRecitationScore,
                'homework_score' => $homeworkScore,
                'whatsapp_score' => $whatsappScore,
                'overall_score' => $overallScore,
                'badge_name' => $badgeName,
                'badge_icon' => $badgeIcon,
                'badge_color' => $badgeColor,
            ];
        }

        // Sort teachers by overall score descending
        usort($stats, function($a, $b) {
            return $b['overall_score'] <=> $a['overall_score'];
        });

        return $stats;
    }

    public function getSummaryStats(): array
    {
        $teacherStats = $this->getTeacherStats();
        $totalTeachers = count($teacherStats);
        
        if ($totalTeachers === 0) {
            return [
                'avg_performance' => 0,
                'top_teacher' => 'لا يوجد',
                'needs_improvement' => 0,
                'total_teachers' => 0,
            ];
        }

        $overallSum = array_column($teacherStats, 'overall_score');
        $avgPerformance = round(array_sum($overallSum) / $totalTeachers);
        
        $topTeacher = $teacherStats[0]['name'] . ' (' . $teacherStats[0]['overall_score'] . '%)';
        
        $needsImprovement = 0;
        foreach ($teacherStats as $stat) {
            if ($stat['overall_score'] < 60) {
                $needsImprovement++;
            }
        }

        return [
            'avg_performance' => $avgPerformance,
            'top_teacher' => $topTeacher,
            'needs_improvement' => $needsImprovement,
            'total_teachers' => $totalTeachers,
        ];
    }
}
