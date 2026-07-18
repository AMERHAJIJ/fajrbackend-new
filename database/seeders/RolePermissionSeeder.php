<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء الصلاحيات الأساسية
        $permissions = [
            // إدارة المستخدمين
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // إدارة الأدوار
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // إدارة الصلاحيات
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            
            // إدارة المواد الدراسية
            'view subjects',
            'create subjects',
            'edit subjects',
            'delete subjects',
            
            // إدارة الحضور
            'view attendance',
            'create attendance',
            'edit attendance',
            'delete attendance',
            
            // إدارة الواجبات
            'view homeworks',
            'create homeworks',
            'edit homeworks',
            'delete homeworks',
            
            // إدارة التسجيلات
            'view recitations',
            'create recitations',
            'edit recitations',
            'delete recitations',
            
            // إدارة الفيديوهات
            'view videos',
            'create videos',
            'edit videos',
            'delete videos',
            
            // إدارة المدونات
            'view blogs',
            'create blogs',
            'edit blogs',
            'delete blogs',
            
            // إدارة الملفات
            'view files',
            'create files',
            'edit files',
            'delete files',
            
            // إدارة الاختبارات
            'view quizzes',
            'create quizzes',
            'edit quizzes',
            'delete quizzes',
            
            // إدارة الجلسات القادمة
            'view next_recitations',
            'create next_recitations',
            'edit next_recitations',
            'delete next_recitations',
            
            // إدارة التصنيفات
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // إدارة العروض التقديمية
            'view sliders',
            'create sliders',
            'edit sliders',
            'delete sliders',
            
            // إدارة الواجبات
            'view homeworks',
            'create homeworks',
            'edit homeworks',
            'delete homeworks',
            
            // تصدير بيانات الطلاب
            'export students',

            // إدارة الجلسات المباشرة
            'view live_sessions',
            'create live_sessions',
            'edit live_sessions',
            'delete live_sessions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // إنشاء الأدوار الأساسية
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $parentRole = Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        // صلاحيات المدير (كل الصلاحيات)
        $adminRole->syncPermissions(Permission::all());

        // صلاحيات المعلم
        $teacherPermissions = [
            'view users',
            'view roles',
            'view permissions',
            'view subjects',
            'create subjects',
            'edit subjects',
            'view homeworks',
            'create homeworks',
            'edit homeworks',
            'view attendance',
            'create attendance',
            'edit attendance',
            'view recitations',
            'create recitations',
            'edit recitations',
            'view blogs',
            'create blogs',
            'edit blogs',
            'view quizzes',
            'create quizzes',
            'edit quizzes',
            'view next_recitations',
            'create next_recitations',
            'edit next_recitations',
            'delete next_recitations',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view sliders',
            'create sliders',
            'edit sliders',
            'delete sliders',
            // الجلسات المباشرة
            'view live_sessions',
            'create live_sessions',
            'edit live_sessions',
            'delete live_sessions',
        ];
        $teacherRole->syncPermissions($teacherPermissions);

        // إعطاء صلاحيات محدودة للطالب
        $studentPermissions = [
            'view subjects',
            'view attendance',
            'view homeworks',
            'view recitations',
            'view videos',
            'view blogs',
            'view quizzes',
            'view next_recitations',
            'view categories',
            'view sliders',
            'view live_sessions',
        ];
        $studentRole->syncPermissions($studentPermissions);

        // إنشاء مستخدم مدير افتراضي
        $adminUser = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@fajr.com',
                'password' => Hash::make('password'),
                'birthday' => '1990-01-01',
                'phone' => '0123456789',
                'address' => 'عنوان افتراضي',
                'active' => true,
            ]
        );

        $adminUser->syncRoles([$adminRole]);

        // إنشاء مستخدم معلم افتراضي
        $teacherUser = User::firstOrCreate(
            ['username' => 'teacher'],
            [
                'name' => 'معلم افتراضي',
                'email' => 'teacher@fajr.com',
                'password' => Hash::make('password'),
                'birthday' => '1990-01-01',
                'phone' => '0123456790',
                'address' => 'عنوان افتراضي',
                'active' => true,
            ]
        );

        $teacherUser->syncRoles([$teacherRole]);

        // إنشاء مستخدم طالب افتراضي
        $studentUser = User::firstOrCreate(
            ['username' => 'student'],
            [
                'name' => 'طالب افتراضي',
                'email' => 'student@fajr.com',
                'password' => Hash::make('password'),
                'birthday' => '2005-01-01',
                'phone' => '0123456791',
                'address' => 'عنوان افتراضي',
                'active' => true,
            ]
        );

        $studentUser->syncRoles([$studentRole]);
    }
}
