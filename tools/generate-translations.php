<?php

/**
 *  Copyright (c) 2021 cooldogedev
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */

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
