<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $studentRole = Role::where('name', 'Student')->firstOrFail();
        
        // Create 10 students
        for ($i = 1; $i <= 10; $i++) {
            $student = User::create([
                'name' => 'طالب ' . $i,
                'username' => 'student' . $i,
                'email' => 'student' . $i . '@example.com',
                'password' => Hash::make('password'),
                'birthday' => now()->subYears(rand(15, 25))->format('Y-m-d'),
                'phone' => '05' . rand(10000000, 99999999),
                'address' => 'عنوان افتراضي ' . $i,
                'email_verified_at' => now(),
            ]);
            
            // Assign student role
            $student->assignRole($studentRole);
        }
        
        $this->command->info('تم إنشاء 10 طلاب بنجاح!');
        $this->command->info('يمكنك تسجيل الدخول باستخدام:');
        $this->command->info('البريد الإلكتروني: student1@example.com');
        $this->command->info('كلمة المرور: password');
    }
}
