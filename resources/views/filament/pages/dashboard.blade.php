<x-filament-panels::page>
    <div class="space-y-6">
        <!-- عنوان الترحيب -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    مرحباً، {{ auth()->user()->name }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    نظام إدارة التعليم - فجر
                </p>
            </div>
        </div>

        <!-- الإحصائيات -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($this->getWidgets() as $widget)
                @livewire($widget)
            @endforeach
        </div>

        <!-- الروابط السريعة -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    الروابط السريعة
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @can('view subjects')
                        <a href="{{ route('filament.admin.resources.subjects.index') }}" 
                           class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                            <x-heroicon-o-academic-cap class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3" />
                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">المواد الدراسية</span>
                        </a>
                    @endcan

                    @can('view attendance')
                        <a href="{{ route('filament.admin.resources.attendances.index') }}" 
                           class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" />
                            <span class="text-sm font-medium text-green-900 dark:text-green-100">الحضور</span>
                        </a>
                    @endcan

                    @can('view recitations')
                        <a href="{{ route('filament.admin.resources.recitation-records.index') }}" 
                           class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                            <x-heroicon-o-microphone class="w-6 h-6 text-purple-600 dark:text-purple-400 mr-3" />
                            <span class="text-sm font-medium text-purple-900 dark:text-purple-100">تسجيلات التلاوة</span>
                        </a>
                    @endcan

                    @can('view quizzes')
                        <a href="{{ route('filament.admin.resources.quizzes.index') }}" 
                           class="flex items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" />
                            <span class="text-sm font-medium text-yellow-900 dark:text-yellow-100">الاختبارات</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
