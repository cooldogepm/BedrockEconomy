<?php

declare(strict_types=1);

function main(): void
{
    $license = file_get_contents(__DIR__ . "/../LICENSE");

    $commentedLicense = "/**" . PHP_EOL;
    $commentedLicense .= implode(PHP_EOL, array_map(fn ($line) => " *" . (trim($line) === "" ? "" : " " . $line), explode(PHP_EOL, $license)));
    $commentedLicense .= PHP_EOL . " * @auto-license";
    $commentedLicense .= PHP_EOL . " */";

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . "/../src")
    );

    /**
     * @var SplFileInfo $file
     */
    foreach ($files as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $filename = $file->getPathname();

        if (!str_contains($filename, ".php")) {
            continue;
        }

        $content = file_get_contents($filename);

        if (str_contains($content, "@auto-license")) {
            continue;
        }

        $content = str_replace("<?php", "<?php" . PHP_EOL . PHP_EOL . trim($commentedLicense), $content);

        file_put_contents($filename, $content);
    }
}

main();
