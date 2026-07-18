<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Filament'));
foreach($files as $file) {
    if ($file->getExtension() == 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match('/[\x{0600}-\x{06FF}]+/u', $content)) {
            echo $file->getPathname() . PHP_EOL;
        }
    }
}
