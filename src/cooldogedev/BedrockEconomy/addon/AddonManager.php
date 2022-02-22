<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\addon;

use cooldogedev\BedrockEconomy\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\BedrockEconomy;

final class AddonManager extends BedrockEconomyOwned
{
    public const ADDONS_MAP = [
        "scorehud" => "cooldogedev\\BedrockEconomy\\addon\\scorehud\\ScoreHudAddon",
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
            $this->getPlugin()->getLogger()->critical("Addon " . $addonName . " is not an instance of BedrockEconomy\addon\Addon");
            return false;
        }

        if (
            $addon->getMinimumSupportedBedrockEconomyVersion() !== Addon::SUPPORTED_BEDROCK_VERSION_ALL &&
            version_compare($this->getPlugin()->getDescription()->getVersion(), $addon->getVersion()) < 0
        ) {
            $this->getPlugin()->getLogger()->critical("Addon " . $addonName . " is not compatible with this version of BedrockEconomy, supported version: " . $addon->getMinimumSupportedBedrockEconomyVersion() . " current version: " . $this->getPlugin()->getDescription()->getVersion());
            return false;
        }

        if ($this->isAddonLoaded($addonName)) {
            $this->getPlugin()->getLogger()->critical("Addon " . $addonName . " is already loaded");
            return false;
        }

        if (!$addon->isLoadable()) {
            $this->getPlugin()->getLogger()->critical("Addon " . $addonName . " failed to load");
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
