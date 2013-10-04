#!/usr/bin/env php
<?php
namespace SmartyTranslate;

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
    die("You should run scanner in cli mode.");
}

echo $gtGenerator->generate();