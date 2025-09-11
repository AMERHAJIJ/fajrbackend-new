<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Attendance;
use App\Models\RecitationRecord;
use App\Models\NextRecitation;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentReportExport
{
    protected $subjectId;
    protected $date;

    public function __construct($subjectId = null, $date = null)
    {
        $this->subjectId = $subjectId;
        $this->date = $date ?: now()->format('Y-m-d');
    }

    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set RTL direction
        $sheet->setRightToLeft(true);
        
        // Set headers
        $headers = [
            'اسم الطالب',
            'حالة الحضور',
            'درجة الحفظ',
            'التسميع القادم',
            'ملاحظات',
            'التاريخ',
        ];
        
        // Add headers with styling
        $sheet->fromArray([$headers], null, 'A1');
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'f1f1f1']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Get the data
        $students = User::role('student')
            ->when($this->subjectId, function($query) {
                $query->whereHas('subjectsAsStudent', function($q) {
                    $q->where('subjects.id', $this->subjectId);
                });
            })
            ->with([
                'attendances' => function($query) {
                    $query->whereDate('date', $this->date);
                },
                'recitationRecords' => function($query) {
                    $query->latest()->take(1);
                },
                'nextRecitations.surah'
            ])
            ->get();
        
        // Add data rows
        $row = 2;
        foreach ($students as $student) {
            $attendance = $student->attendances->first();
            $recitationRecord = $student->recitationRecords->first();
            $nextRecitation = $student->nextRecitations->first();
            
            $sheet->setCellValue('A' . $row, $student->name);
            $sheet->setCellValue('B' . $row, $attendance ? ($attendance->status === 'present' ? 'حاضر' : 'غائب') : 'لم يتم التسجيل');
            $sheet->setCellValue('C' . $row, $recitationRecord ? $recitationRecord->score . '/10' : 'لا يوجد');
            $sheet->setCellValue('D' . $row, $nextRecitation ? ($nextRecitation->surah ? $nextRecitation->surah->name . ' - ' . $nextRecitation->from_verse . ':' . $nextRecitation->to_verse : '') : 'لا يوجد');
            $sheet->setCellValue('E' . $row, $recitationRecord ? $recitationRecord->notes : '');
            $sheet->setCellValue('F' . $row, $this->date);
            
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set general cell styling
        $sheet->getStyle('A2:F' . ($row-1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Create a temporary file
        $fileName = 'تقرير_الطلاب_' . $this->date . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
