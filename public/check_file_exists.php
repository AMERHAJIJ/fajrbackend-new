<?php
header('Content-Type: text/plain; charset=utf-8');

$paths = [
    '/home/u372267104/domains/alfajr.tech/public_html/storage/app/public/google-service-account.json',
    __DIR__ . '/../storage/app/public/google-service-account.json',
    __DIR__ . '/storage/google-service-account.json'
];

echo "Checking file paths:\n";
foreach ($paths as $path) {
    $exists = file_exists($path);
    echo "- Path: $path | Exists: " . ($exists ? "YES" : "NO");
    if ($exists) {
        $content = file_get_contents($path);
        $json = json_decode($content, true);
        echo " | Valid JSON: " . ($json !== null ? "YES" : "NO");
        if ($json === null) {
            echo " | Error: " . json_last_error_msg();
        } else {
            echo " | Client Email: " . ($json['client_email'] ?? 'NOT FOUND');
        }
    }
    echo "\n";
}

echo "\nChecking GoogleSheetsService initialization:\n";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $service = app(\App\Services\GoogleSheetsService::class);
    echo "SUCCESS: GoogleSheetsService initialized successfully!\n";
    
    $reflection = new ReflectionClass($service);
    $spreadsheetIdProp = $reflection->getProperty('spreadsheetId');
    $spreadsheetIdProp->setAccessible(true);
    $spreadsheetId = $spreadsheetIdProp->getValue($service);
    echo "Spreadsheet ID: $spreadsheetId\n";
    
    $test = false;
    try {
        $sheetsService->spreadsheets->get($spreadsheetId);
        $test = true;
    } catch (\Exception $ex) {
        echo "Spreadsheets Get ERROR: " . $ex->getMessage() . "\n";
    }
    echo "Test Connection: " . ($test ? "SUCCESS" : "FAILED") . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
