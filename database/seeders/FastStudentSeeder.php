<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class FastStudentSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = User::firstOrCreate(
                ['username' => "tester_student_$i"],
                [
                    'name' => "New Student $i",
                    'email' => "test$i@fajr.com",
                    'password' => bcrypt('password'),
                    'birthday' => '2005-01-01',
                    'phone' => "051234567$i",
                    'address' => 'Test Address',
                ]
            );
            $user->assignRole('student');
        }
    }
}
