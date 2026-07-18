<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$records = \App\Models\VideoAnalytic::all();
$data = $records->map(fn ($r) => [
    'id' => $r->id,
    'watched_duration' => $r->watched_duration,
    'pause_count' => $r->pause_count,
    'forward_skip_count' => $r->forward_skip_count,
    'backward_skip_count' => $r->backward_skip_count,
    'playback_rate' => $r->playback_rate,
    'app_switch_count' => $r->app_switch_count,
])->values()->toJson();

$pythonPath = 'python';
$scriptPath = base_path('ai_engine/detect_anomalies.py');

$escapedData = escapeshellarg($data);
$command = "{$pythonPath} {$scriptPath} {$escapedData}";

echo "Command: " . $command . "\n";
$output = shell_exec($command);
echo "Output: " . $output . "\n";
$results = json_decode($output, true);
echo "JSON: " . print_r($results, true) . "\n";
