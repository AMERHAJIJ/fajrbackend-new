<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = \Illuminate\Http\Request::create('/api/homeworks', 'GET');
$user = \App\Models\User::where('username', 'student')->first();
$request->setUserResolver(function() use ($user) { return $user; });

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
