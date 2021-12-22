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

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\listener;

use cooldogedev\BedrockEconomY\api\BedrockEconomyOwned;
use cooldogedev\BedrockEconomy\constant\SearchConstants;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;

final class PlayerListener extends BedrockEconomyOwned implements Listener
{
    /**
     * @param PlayerLoginEvent $event
     * @ignoreCanceled true
     */
    public function onPlayerLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$this->getPlugin()->getAccountManager()->hasAccount($player->getXuid()) && !$this->getPlugin()->getAccountManager()->hasAccount($player->getName(), SearchConstants::SEARCH_MODE_USERNAME)) {
            $this->getPlugin()->getAccountManager()->addAccount($player->getXuid(), $player->getName());
            $this->getPlugin()->getLogger()->debug("Creating a new record for " . $player->getName() . ".");
        } else {
            $session = $this->getPlugin()->getAccountManager()->getAccount($player->getName(), SearchConstants::SEARCH_MODE_USERNAME);
            if ($session->isXuidIsInvalid()) {
                $session->attemptXuidFix($player->getXuid());
                $this->getPlugin()->getLogger()->debug("Fixing " . $player->getName() . "'s account data (xuid).");
            }
        }
    }

//    public function onPlayerQuit(PlayerQuitEvent $event): void
//    {
//        $player = $event->getPlayer();
//        if ($this->getPlugin()->getDatabaseManager()->getSaveMode() === SaveConstants::SAVE_MODE_UPON_DISCONNECTION) {
//            $this->getPlugin()->getLogger()->debug("Saving " . $player->getName() . "'s account data.");
//        }
//    }
}
