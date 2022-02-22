<?php

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
