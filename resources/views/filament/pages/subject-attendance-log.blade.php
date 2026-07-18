@php
    $attendancesGrouped = $subject->attendances()
        ->with('student.parent')
        ->orderBy('date', 'desc')
        ->get()
        ->groupBy(function($a) {
            return \Carbon\Carbon::parse($a->date)->format('Y-m-d');
        });
@endphp

<div class="space-y-4">
    @if ($attendancesGrouped->isEmpty())
        <div class="text-center py-6 text-gray-500">
            لا توجد سجلات حضور وغياب مسجلة لهذه الحلقة بعد.
        </div>
    @else
        @foreach ($attendancesGrouped as $date => $records)
            <div x-data="{ open: false }" class="border border-gray-200 rounded-lg overflow-hidden dark:border-gray-700">
                <button @click="open = !open" type="button" class="flex justify-between items-center w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-150">
                    <span class="font-medium text-gray-700 dark:text-gray-200 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>التاريخ: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                    </span>
                    <span class="flex items-center space-x-2">
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-semibold">
                            الحاضرين: {{ $records->where('status', true)->count() }} / {{ $records->count() }}
                        </span>
                        <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </button>
                <div x-show="open" x-collapse class="p-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">الطالب</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">إرسال واتساب للغياب</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($records as $record)
                                    <tr>
                                        <td class="px-3 py-3 text-sm text-gray-900 dark:text-gray-100 font-medium">
                                            {{ $record->student->name }}
                                        </td>
                                        <td class="px-3 py-3 text-center text-sm">
                                            @if ($record->status)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    حاضر
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    غائب
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-left">
                                            @if (!$record->status)
                                                @php
                                                    $student = $record->student;
                                                    $parent = $student->parent;
                                                    $fatherPhone = $student->father_phone ?? ($parent ? $parent->phone : null);
                                                    $motherPhone = $student->mother_phone;
                                                    
                                                    $dateFormatted = \Carbon\Carbon::parse($record->date)->format('d/m/Y');
                                                    $msg = "السلام عليكم ورحمة الله وبركاته،\nنحيطكم علماً بغياب الطالب ({$student->name}) اليوم {$dateFormatted} عن حلقة ({$subject->title}). يرجى المتابعة وتأكيد سبب الغياب.";
                                                    $encodedMsg = urlencode($msg);
                                                    
                                                    $cleanFather = $fatherPhone ? preg_replace('/[^0-9]/', '', $fatherPhone) : null;
                                                    if ($cleanFather) {
                                                        if (str_starts_with($cleanFather, '0')) $cleanFather = '90' . substr($cleanFather, 1);
                                                        if (!str_starts_with($cleanFather, '90') && strlen($cleanFather) == 10 && str_starts_with($cleanFather, '5')) $cleanFather = '90' . $cleanFather;
                                                    }
                                                    
                                                    $cleanMother = $motherPhone ? preg_replace('/[^0-9]/', '', $motherPhone) : null;
                                                    if ($cleanMother) {
                                                        if (str_starts_with($cleanMother, '0')) $cleanMother = '90' . substr($cleanMother, 1);
                                                        if (!str_starts_with($cleanMother, '90') && strlen($cleanMother) == 10 && str_starts_with($cleanMother, '5')) $cleanMother = '90' . $cleanMother;
                                                    }
                                                @endphp
                                                <div class="flex space-x-2 justify-end">
                                                    @if ($cleanFather)
                                                        <a href="https://api.whatsapp.com/send?phone={{ $cleanFather }}&text={{ $encodedMsg }}" target="_blank" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-semibold rounded bg-green-600 hover:bg-green-700 text-white transition duration-150">
                                                            واتساب الأب
                                                        </a>
                                                    @endif
                                                    @if ($cleanMother)
                                                        <a href="https://api.whatsapp.com/send?phone={{ $cleanMother }}&text={{ $encodedMsg }}" target="_blank" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-semibold rounded bg-emerald-600 hover:bg-emerald-700 text-white transition duration-150">
                                                            واتساب الأم
                                                        </a>
                                                    @endif
                                                    @if (!$cleanFather && !$cleanMother && $student->phone)
                                                        @php
                                                            $cleanStudent = preg_replace('/[^0-9]/', '', $student->phone);
                                                            if (str_starts_with($cleanStudent, '0')) $cleanStudent = '90' . substr($cleanStudent, 1);
                                                        @endphp
                                                        <a href="https://api.whatsapp.com/send?phone={{ $cleanStudent }}&text={{ $encodedMsg }}" target="_blank" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-semibold rounded bg-blue-600 hover:bg-blue-700 text-white transition duration-150">
                                                            واتساب الطالب
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
