<?php

/**
 *  Copyright (c) 2022 cooldogedev
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

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\addon;

use cooldogedev\BedrockEconomy\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use pocketmine\plugin\PluginBase;

abstract class Addon extends BedrockEconomyOwned
{
    public const SUPPORTED_BEDROCK_VERSION_ALL = "all";

    protected bool $enabled;

    final public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);

        $this->enabled = false;
    }

    /**
     * The name of the addon, must be unique.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * The version of the addon.
     *
     * @return string
     */
    abstract public function getVersion(): string;

    /**
     * The minimum supported version of BedrockEconomy.
     * If the version is @return string
     * @link Addon::SUPPORTED_BEDROCK_VERSION_ALL, the addon will be enabled on all BedrockEconomy versions.
     *
     */
    public function getMinimumSupportedBedrockEconomyVersion(): string
    {
        return Addon::SUPPORTED_BEDROCK_VERSION_ALL;
    }

    /**
     * Called before a plugin is enabled, this should be only used for dependency checking.
     *
     * @return bool
     */
    public function isLoadable(): bool
    {
        return false;
    }

    /**
     * Returns whether the addon is enabled or not.
     *
     * @return bool
     */
    final public function isEnabled(): bool
    {
        return $this->enabled;
    }

    final public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;

        if ($enabled) {
            $this->onEnable();
        } else {
            $this->onDisable();
        }
    }

    /**
     * Called when the plugin is enabled. Similar to @link PluginBase::onEnable()
     * Should be used for listeners registration and such logic.
     */
    protected function onEnable(): void
    {
    }

    /**
     * Called when the addon is disabled. Similar to @link PluginBase::onDisable()
     */
    protected function onDisable(): void
    {
    }
}
