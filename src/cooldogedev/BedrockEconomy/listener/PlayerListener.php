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

use cooldogedev\BedrockEconomy\constant\SessionConstants;
use cooldogedev\BedrockEconomY\interfaces\BedrockEconomyOwned;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

final class PlayerListener extends BedrockEconomyOwned implements Listener
{
    /**
     * @param PlayerLoginEvent $event
     * @ignoreCanceled true
     */
    public function onPlayerLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$this->getPlugin()->getSessionManager()->hasSession($player->getXuid())) {
            $this->getPlugin()->getSessionManager()->createSession($player->getXuid(), $player->getName());
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getXuid());
        if ($this->getPlugin()->getDatabaseManager()->getSaveMode() === SessionConstants::SESSION_SAVE_MODE_UPON_DISCONNECTION && $session->onSave()) {
            $this->getPlugin()->getLogger()->debug("Saving " . $player->getName() . "'s session.");
        }
    }
}
