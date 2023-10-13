<?php

declare(strict_types=1);

function main(): void
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../src')
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

        if (!str_contains($content, "@auto-license")) {
            continue;
        }

        $content = substr($content, strpos($content, "\n */") + strlen("\n */"));
        $content = "<?php" . $content;

        file_put_contents($file->getPathname(), $content);
    }
}

main();
