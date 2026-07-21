<?php
header('Content-Type: text/plain');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GoogleSheetsService;

$service = app(GoogleSheetsService::class);
$reflection = new ReflectionClass($service);
$serviceProp = $reflection->getProperty('service');
$serviceProp->setAccessible(true);
$sheetsService = $serviceProp->getValue($service);

$spreadsheetIdProp = $reflection->getProperty('spreadsheetId');
$spreadsheetIdProp->setAccessible(true);
$spreadsheetId = $spreadsheetIdProp->getValue($service);

echo "Hostinger Spreadsheet ID: " . $spreadsheetId . "\n";

try {
    $spreadsheet = $sheetsService->spreadsheets->get($spreadsheetId);
    echo "Spreadsheet Title: " . $spreadsheet->getProperties()->getTitle() . "\n";
    $sheets = $spreadsheet->getSheets();
    echo "Tabs:\n";
    foreach ($sheets as $s) {
        echo "- " . $s->getProperties()->getTitle() . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
