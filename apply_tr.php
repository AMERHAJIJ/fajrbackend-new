<?php
$ar = include('lang/ar/admin.php');
$tr = include('lang/tr/admin.php');

function flatten($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, flatten($value, $prefix . $key . '.'));
        } else {
            $result[$prefix . $key] = $value;
        }
    }
    return $result;
}

$flatAr = flatten($ar);
$flatTr = flatten($tr);

$map = [];
foreach ($flatAr as $key => $arStr) {
    if (isset($flatTr[$key]) && is_string($arStr) && is_string($flatTr[$key])) {
        // Build replacements for string literals
        $map["'".$arStr."'"] = "'".addslashes($flatTr[$key])."'";
        $map['"'.$arStr.'"'] = '"'.addslashes($flatTr[$key]).'"';
    }
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Filament'));
$count = 0;
foreach($files as $file) {
    if ($file->getExtension() == 'php') {
        $content = file_get_contents($file->getPathname());
        $newContent = strtr($content, $map);
        if ($content !== $newContent) {
            file_put_contents($file->getPathname(), $newContent);
            echo "Updated: " . $file->getPathname() . PHP_EOL;
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
