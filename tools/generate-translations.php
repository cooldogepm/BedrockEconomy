<?php

function main(): void
{

    $translations = yaml_parse_file(__DIR__ . DIRECTORY_SEPARATOR . "en-US.yml");

    $template = "<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\language;

final class KnownTranslations
{
__CODE__
}
";

    $code = "";
    $indentation = str_repeat(" ", 5);

    $first = true;

    foreach ($translations as $key => $message) {

        if (is_array($message)) {
            continue;
        }

        $message = str_replace("-", "_", strtoupper($key));

        !$first && $code .= PHP_EOL;

        $code .= $indentation . "public const " . $message . " = \"$key\";";

        if ($first) {
            $first = false;
        }
    }

    file_put_contents("KnownTranslations.php", str_replace("__CODE__", $code, $template));
}

main();
