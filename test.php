<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = \App\Models\User::where('username', 'student')->first();
if(!$user) die('No user');
$subjectIds = $user->subjectsAsStudent()->pluck('subjects.id');
$hw = \App\Models\Homework::with(['subject', 'teacher:id,name'])->whereIn('subject_id', $subjectIds)->where('active', true)->latest()->get();
echo json_encode($hw);
