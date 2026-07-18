<x-filament-panels::page>
    @php
        $summary = $this->getSummaryStats();
        $teachers = $this->getTeacherStats();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Card 1: Total Teachers -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex items-center space-x-4 space-x-reverse">
            <div class="p-3 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 005.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي المعلمين</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['total_teachers'] }}</p>
            </div>
        </div>

        <!-- Card 2: Average Performance -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex items-center space-x-4 space-x-reverse">
            <div class="p-3 bg-success-50 dark:bg-success-900/30 text-success-600 dark:text-success-400 rounded-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">متوسط الأداء العام</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $summary['avg_performance'] }}%</p>
            </div>
        </div>

        <!-- Card 3: Top Teacher -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex items-center space-x-4 space-x-reverse col-span-1 md:col-span-2">
            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">المعلم الأكثر التزاماً (الصدارة)</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $summary['top_teacher'] }}</p>
            </div>
        </div>
    </div>

    <!-- Leaderboard Table Section -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                لوحة تميز وأداء معلمي النادي الصيفي
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                ترتيب المعلمين حسب التزامهم بتسجيل الحضور، التسميع، الواجبات، ومشاركة الواتساب.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900 text-xs font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-4">الترتيب</th>
                        <th class="px-6 py-4">اسم المعلم</th>
                        <th class="px-6 py-4">الحلقة المسؤولة</th>
                        <th class="px-6 py-4">عدد الطلاب</th>
                        <th class="px-6 py-4 text-center">أخذ الحضور</th>
                        <th class="px-6 py-4 text-center">تسجيل التسميع</th>
                        <th class="px-6 py-4 text-center">التسميع القادم</th>
                        <th class="px-6 py-4 text-center">إرسال الواجب</th>
                        <th class="px-6 py-4 text-center">تحديث الواتساب</th>
                        <th class="px-6 py-4 text-center">الأداء العام</th>
                        <th class="px-6 py-4">وسام التميز</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-300">
                    @forelse ($teachers as $index => $teacher)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-6 py-4 font-bold">
                                @if($index == 0)
                                    <span class="text-yellow-600 bg-yellow-50 dark:bg-yellow-950/50 px-2 py-1 rounded-full border border-yellow-200 dark:border-yellow-900">🥇 الأول</span>
                                @elseif($index == 1)
                                    <span class="text-gray-600 bg-gray-50 dark:bg-gray-950/50 px-2 py-1 rounded-full border border-gray-200 dark:border-gray-800">🥈 الثاني</span>
                                @elseif($index == 2)
                                    <span class="text-amber-700 bg-amber-50 dark:bg-amber-950/50 px-2 py-1 rounded-full border border-amber-200 dark:border-amber-900">🥉 الثالث</span>
                                @else
                                    <span class="px-2 py-1 text-gray-500">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-950 dark:text-white">
                                {{ $teacher['name'] }}
                            </td>
                            <td class="px-6 py-4 text-xs max-w-xs truncate">
                                {{ $teacher['subjects'] }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $teacher['students_count'] }} طالب
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-success-600">
                                    {{ $teacher['attendance_score'] }}%
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-success-600">
                                    {{ $teacher['recitation_score'] }}%
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-success-600">
                                    {{ $teacher['next_recitation_score'] }}%
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-success-600">
                                    {{ $teacher['homework_score'] }}%
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-success-600">
                                    {{ $teacher['whatsapp_score'] }}%
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2 space-x-reverse">
                                    <span class="font-bold text-lg {{ $teacher['overall_score'] >= 80 ? 'text-success-600' : ($teacher['overall_score'] >= 50 ? 'text-warning-600' : 'text-danger-600') }}">
                                        {{ $teacher['overall_score'] }}%
                                    </span>
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 h-2 rounded-full hidden md:block overflow-hidden">
                                        <div class="h-full rounded-full {{ $teacher['overall_score'] >= 80 ? 'bg-success-500' : ($teacher['overall_score'] >= 50 ? 'bg-warning-500' : 'bg-danger-500') }}" style="width: {{ $teacher['overall_score'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full border {{ $teacher['badge_color'] }}">
                                    {{ $teacher['badge_icon'] }} {{ $teacher['badge_name'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                                لا يوجد معلمون مسجلون في النظام حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
