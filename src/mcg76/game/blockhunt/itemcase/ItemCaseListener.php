<?php

namespace mcg76\game\blockhunt\itemcase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\event\player\PlayerEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector2;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerKickEvent;
use mcg76\game\blockhunt\arenas\ArenaManager;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\Server;
use mcg76\game\blockhunt\itemcase\ItemCaseBuilder;
use pocketmine\event\player\PlayerItemHeldEvent;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\defence\DefenceManager;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityLevelChangeEvent;
use mcg76\game\blockhunt\itemcase\ItemCaseModel;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\entity\Effect;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use pocketmine\event\entity\EntityDeathEvent;

/**
 * ItemCaseListener - Made by minecraftgenius76
 *
 * You're allowed to use for own usage only "as-is".
 * you're not allowed to republish or resell or for any commercial purpose.
 *
 * Thanks for your cooperate!
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76
 *        
 */
class ItemCaseListener implements Listener {
	public $plugin = null;
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
	}
	public function getPlugin() {
		return $this->plugin;
	}
	
	/**
	 *
	 * @param PlayerRespawnEvent $event        	
	 */
	public function onPlayerSpawn(PlayerRespawnEvent $event) {
		$player = $event->getPlayer ();
		if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
			if (! isset ( $this->getPlugin ()->npcsSpawns [$player->getName ()] )) {
				$this->getPlugin ()->storeCaseManager->npcsSpawns [$player->getName ()] = $player->getName ();
				foreach ( $this->getPlugin ()->getStoreCaseManager ()->storeItemCases as $itemcase ) {
					if ($itemcase instanceof ItemCaseModel) {
						if (strtolower ( $player->getLevel ()->getName () ) === strtolower ( $itemcase->levelName )) {
							ItemCaseBuilder::spawnCaseItem ( $player, $itemcase );
						}
					}
				}
			}
		}
	}
	
	/**
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				$this->getPlugin ()->storeCaseManager->handleTapOnItemCase ( $event );
			}
		}
	}
	public function onPlayerKicked(PlayerKickEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (isset ( $this->getPlugin ()->getStoreCaseManager ()->npcsSpawns [$event->getPlayer ()->getName ()] )) {
				unset ( $this->getPlugin ()->getStoreCaseManager ()->npcsSpawns [$event->getPlayer ()->getName ()] );
			}
		}
	}
	public function onPlayerQuit(PlayerQuitEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (isset ( $this->getPlugin ()->getStoreCaseManager ()->npcsSpawns [$event->getPlayer ()->getName ()] )) {
				unset ( $this->getPlugin ()->getStoreCaseManager ()->npcsSpawns [$event->getPlayer ()->getName ()] );
			}
		}
	}
	public function onEntityDeath(EntityDeathEvent $event) {
		if ($event->getEntity () instanceof Player) {
			$player = $event->getEntity ();
			if (isset ( $this->getPlugin ()->getStoreCaseManager ()->npcsSpawns [$player->getName ()] )) {
				unset ( $this->getPlugin ()->getStoreCaseManager ()->npcsSpawns [$player->getName ()] );
			}
		}
	}
}