<?php

declare(strict_types=1);

const EOL = "\n";

function main(): void
{
    $license = file_get_contents(__DIR__ . "/../LICENSE");
    $license = str_replace("\r\n", "\n", $license);

    $commentedLicense = "/**" . EOL;
    $commentedLicense .= implode(EOL, array_map(fn ($line) => " *" . (trim($line) === "" ? "" : " " . $line), explode(EOL, $license)));
    $commentedLicense .= EOL . " * @auto-license";
    $commentedLicense .= EOL . " */";

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

        $content = str_replace("<?php", "<?php" . EOL . EOL . trim($commentedLicense), $content);

        file_put_contents($filename, $content);
    }
}

main();
