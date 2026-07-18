<?php
header('Content-Type: text/plain');

echo "Executing Artisan cache clear commands...\n";
chdir(__DIR__ . '/..');

echo "Config Clear:\n";
echo shell_exec('php artisan config:clear') . "\n";

echo "Cache Clear:\n";
echo shell_exec('php artisan cache:clear') . "\n";

echo "View Clear:\n";
echo shell_exec('php artisan view:clear') . "\n";

echo "Route Clear:\n";
echo shell_exec('php artisan route:clear') . "\n";

echo "Completed!\n";
