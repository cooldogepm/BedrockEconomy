<?php

$current_year = date('Y');
$prev_year = $current_year - 1;

$path = realpath("../src/cooldogedev/BedrockEconomy/");

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path),
    RecursiveIteratorIterator::LEAVES_ONLY
);

/**
 * @var SplFileInfo $file
 */
foreach ($files as $file) {
    if ($file->getFilename() === "." || $file->getFilename() === "..") {
        continue;
    }

    $file_contents = file_get_contents($file->getRealPath());

    $file_contents = str_replace("Copyright (c) $prev_year", "Copyright (c) $current_year", $file_contents);

    file_put_contents($file->getRealPath(), $file_contents);
}