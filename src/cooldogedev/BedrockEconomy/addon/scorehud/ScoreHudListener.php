<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\addon\scorehud;

use cooldogedev\BedrockEconomy\event\transaction\TransactionProcessEvent;
use cooldogedev\BedrockEconomy\transaction\types\TransferTransaction;
use cooldogedev\BedrockEconomy\transaction\types\UpdateTransaction;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

final class ScoreHudListener implements Listener
{
    public function __construct(protected ScoreHudAddon $parent)
    {
    }

    /**
     * Caches the initial balance from the db.
     *
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $this->getParent()->initializePlayerCache($player->getName());
    }

    public function getParent(): ScoreHudAddon
    {
        return $this->parent;
    }

    /**
     * Sends the tag to the player.
     *
     * @param PlayerLoginEvent $event
     */
    public function onPlayerJoin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $balance = $this->getParent()->getPlayerCache($player->getName());

        $event = new PlayerTagUpdateEvent($player, new ScoreTag(ScoreHudAddon::SCOREHUD_TAG_BALANCE, $balance !== null ? (string)$balance : "N/A"));
        $event->call();
    }

    /**
     * Removes the player from the cache.
     *
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $this->getParent()->removePlayerCache($player->getName());
    }

    /**
     * Updates the tag when the balance changes if the player is connected to the server.
     *
     * @param TransactionProcessEvent $event
     */
    public function onTransactionProcess(TransactionProcessEvent $event): void
    {
        $transaction = $event->getTransaction();

        if ($transaction instanceof UpdateTransaction) {
            $this->getParent()->updatePlayerCache($transaction->getTarget());
        } elseif ($transaction instanceof TransferTransaction) {
            $this->getParent()->updatePlayerCache($transaction->getSender());
            $this->getParent()->updatePlayerCache($transaction->getReceiver());
        }
    }

    /**
     * Intercepts the tag resolve event and adds the balance tag.
     *
     * @param TagsResolveEvent $event
     */
    public function onTagResolve(TagsResolveEvent $event): void
    {
        $player = $event->getPlayer();
        $tag = $event->getTag();

        switch ($tag->getName()) {
            case ScoreHudAddon::SCOREHUD_TAG_BALANCE:
                $tag->setValue((string)($this->getParent()->getPlayerCache($player->getName()) ?? "N/A"));
        }
    }
}
