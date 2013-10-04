#!/usr/bin/env php
<?php
ini_set('display_errors', 'off');

use SmartyTranslate\Extractor;
use SmartyTranslate\GettextCli;
use SmartyTranslate\GettextGenerator;

include "Vendor/autoload.php";

$extractor = new Extractor();
$gtCli = new GettextCli($_SERVER['argv']);
$gtGenerator = new GettextGenerator();

if (php_sapi_name() === 'cli') {
    foreach ($gtCli->getFiles() as $file) {
        $extractor->extractFile($file);
    }
} else {
    die("<br><b>You should run scanner.php in cli mode.</b>");
}

echo $gtGenerator->generate();