<?php

/**
 * MIT License
 *
 * Copyright (c) 2021-2024 cooldogedev
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @auto-license
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\language;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use pocketmine\utils\TextFormat;

final class LanguageManager
{
    private const DEFAULT_LANGUAGE = "en-US";
    private const SUPPORTED_LANGUAGES = [
        "de-CH",
        "en-US",
        "es-ES",
    ];

    private static string $language;
    private static array $translations;

    public static function init(BedrockEconomy $plugin, ?string $language): void
    {
        $languagesFolder = $plugin->getDataFolder() . "languages";
        @mkdir($languagesFolder);

        foreach (LanguageManager::SUPPORTED_LANGUAGES as $languageCode) {
            $plugin->saveResource("languages" . DIRECTORY_SEPARATOR . $languageCode . ".yml");
        }

        if (!$language || !in_array($language, LanguageManager::SUPPORTED_LANGUAGES)) {
            $language = LanguageManager::DEFAULT_LANGUAGE;
        }

        LanguageManager::$language = $language;
        LanguageManager::$translations = yaml_parse_file(
            $languagesFolder . DIRECTORY_SEPARATOR . $language . ".yml"
        );
    }

    public static function getString(string $translation, array $variables = []): string
    {
        return isset(LanguageManager::$translations[$translation]) ?
            TextFormat::colorize(LanguageManager::translate($translation, $variables)) :
            "Translation not found: " . $translation;
    }

    public static function getArray(string $translation): ?array
    {
        return (array)LanguageManager::$translations[$translation] ?? null;
    }

    public static function getLanguage(): string
    {
        return LanguageManager::$language;
    }

    private static function translate(string $translation, array $variables = []): string
    {
        return str_replace(array_keys($variables), array_values($variables), LanguageManager::$translations[$translation]);
    }
}