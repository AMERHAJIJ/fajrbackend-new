<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Filament'));
$arabicStrings = [];

foreach($files as $file) {
    if ($file->getExtension() == 'php') {
        $content = file_get_contents($file->getPathname());
        // Match all strings containing Arabic characters enclosed in single or double quotes
        if (preg_match_all("/'([^']*[\x{0600}-\x{06FF}]+[^']*)'|\"([^\"]*[\x{0600}-\x{06FF}]+[^\"]*)\"/u", $content, $matches)) {
            foreach ($matches[1] as $match) {
                if (!empty($match)) $arabicStrings[$match] = true;
            }
            foreach ($matches[2] as $match) {
                if (!empty($match)) $arabicStrings[$match] = true;
            }
        }
    }
}

file_put_contents('arabic_strings.json', json_encode(array_keys($arabicStrings), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Found " . count($arabicStrings) . " unique Arabic strings.";
