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

final class AddonManager extends BedrockEconomyOwned
{
    public const ADDONS_MAP = [
        "ScoreHud" => "cooldogedev\\BedrockEconomy\\addon\\scorehud\\ScoreHudAddon",
    ];

    /**
     * @var Addon[]
     */
    protected array $addons;

    public function __construct(BedrockEconomy $plugin)
    {
        parent::__construct($plugin);

        $this->addons = [];

        $this->loadAddons();
    }

    protected function loadAddons(): void
    {
        foreach (AddonManager::ADDONS_MAP as $addonName => $addonClass) {
            $this->loadAddon($addonName, $addonClass);
        }
    }

    public function loadAddon(string $addonName, string $addonClass): bool
    {
        $addon = new $addonClass($this->plugin);

        if (!$addon instanceof Addon) {
            $this->getPlugin()->getLogger()->debug("Addon " . $addonName . " is not an instance of BedrockEconomy\addon\Addon");
            return false;
        }

        if (
            $addon->getMinimumSupportedBedrockEconomyVersion() !== Addon::SUPPORTED_BEDROCK_VERSION_ALL &&
            version_compare($this->getPlugin()->getDescription()->getVersion(), $addon->getVersion()) < 0
        ) {
            $this->getPlugin()->getLogger()->debug("Addon " . $addonName . " is not compatible with this version of BedrockEconomy, supported version: " . $addon->getMinimumSupportedBedrockEconomyVersion() . " current version: " . $this->getPlugin()->getDescription()->getVersion());
            return false;
        }

        if ($this->isAddonLoaded($addonName)) {
            $this->getPlugin()->getLogger()->debug("Addon " . $addonName . " is already loaded");
            return false;
        }

        if (!$addon->isLoadable()) {
            $this->getPlugin()->getLogger()->debug("Addon " . $addonName . " failed to load");
            return false;
        }

        $addon->setEnabled(true);

        $this->addons[$addon->getName()] = $addon;

        $this->getPlugin()->getLogger()->debug("Addon " . $addonName . " was loaded");

        return true;
    }

    public function isAddonLoaded(string $addon): bool
    {
        return isset($this->addons[$addon]) && $this->addons[$addon]->isLoaded();
    }

    public function getAddon(string $name): ?Addon
    {
        return $this->addons[$name] ?? null;
    }

    /**
     * @return Addon[]
     */
    public function getAddons(): array
    {
        return $this->addons;
    }
}
