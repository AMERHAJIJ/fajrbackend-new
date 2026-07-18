<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::where('username', 'student')->first();
        if (!$user) return;

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\WelcomeNotification',
            'data' => [
                'title' => 'Hoş Geldiniz!',
                'body' => 'El Fajr eğitim uygulamasına hoş geldiniz.',
                'icon' => 'celebration',
                'color' => 'gold',
            ],
            'read_at' => null,
        ]);

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\HomeworkNotification',
            'data' => [
                'title' => 'Yeni Ödev Eklendi',
                'body' => 'Matematik dersi için yeni bir ödeviniz var. Hemen göz atın!',
                'icon' => 'assignment',
                'color' => 'blue',
            ],
            'read_at' => null,
        ]);

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\LiveSessionNotification',
            'data' => [
                'title' => 'Canlı Ders Başlıyor',
                'body' => 'Fizik canlı dersi 5 dakika içinde başlayacaktır.',
                'icon' => 'video_camera_front',
                'color' => 'red',
            ],
            'read_at' => null,
        ]);
    }
}
