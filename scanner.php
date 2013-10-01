#!/usr/bin/env php
<?php

function __autoload($class_name)
{
    include $class_name . '.php';
}

$extractor = new Extractor();
$potGenerator = new PotGenerator();

for ($ac = 1; $ac < $_SERVER['argc']; $ac++) {
    $extractor->extractFile($_SERVER['argv'][$ac]);
}

$potGenerator->setPotHeader(
    array(
        'Project-Id-Version' => 'Neoscriber',
        'Report-Msgid-Bugs-To' => 'm.abdolirad@gmail.com',
        'Last-Translator' => 'Mohammad Abdoli Rad <m.abdolirad@gmail.com>',
        'Language-Team' => 'Persian',
        'Language' => 'Persian',
    )
);

$potGenerator->generate();