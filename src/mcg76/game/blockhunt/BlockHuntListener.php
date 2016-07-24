<?php

namespace mcg76\game\blockhunt;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\level\Position;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\tile\Sign;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
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
use pocketmine\Server;
use mcg76\game\blockhunt\itemcase\ItemCaseBuilder;
use pocketmine\event\player\PlayerItemHeldEvent;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\defence\DefenceManager;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityLevelChangeEvent;
use mcg76\game\blockhunt\itemcase\ItemCaseModel;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\event\entity\EntityDeathEvent;

/**
 * MCPE BlockHunt Minigame - Made by minecraftgenius76
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
class BlockHuntListener extends MiniGameBase implements Listener {
	public function __construct(BlockHuntPlugIn $plugin) {
		parent::__construct ( $plugin );
	}
	public function onPlayerJoin(PlayerJoinEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (! $event->getPlayer ()->hasPermission ( BlockHuntController::BH_PERMISSION_PLAY )) {
				$event->getPlayer ()->addAttachment ( $this->getPlugin (), BlockHuntController::BH_PERMISSION_PLAY, true );
			}
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				$this->getPlugin ()->controller->handlePlayerRejoinThePlay ( $event->getPlayer () );
			}
		}
	}
	public function onPlayerSpawn(PlayerRespawnEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (! $event->getPlayer ()->hasPermission ( BlockHuntController::BH_PERMISSION_PLAY )) {
				$event->getPlayer ()->addAttachment ( $this->getPlugin (), BlockHuntController::BH_PERMISSION_PLAY, true );
			}
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				$this->getPlugin ()->controller->handlePlayerRejoinThePlay ( $event->getPlayer () );
			}
		}
	}
	
	/**
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) {
		$b = $event->getBlock ();
		if ($this->getPlugin ()->pos_display_flag === 1) {
			$event->getPlayer ()->sendMessage ( "TOUCHED: [x=" . $b->x . " y=" . $b->y . " z=" . $b->z . "]" );
		}
		$player = $event->getPlayer ();
		$block = $event->getBlock ();
		if ($player instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				$this->getPlugin ()->controller->handleTapOnHiderBlock ( $player, $block );
				$this->tapOnServerHomeSign ( new Position ( $block->x, $block->y, $block->z, $block->getLevel () ), $player );
			}
		}
	}
	
	/**
	 *
	 * @param BlockBreakEvent $event
	 *        	@priority MONITOR
	 */
	public function onBlockBreak(BlockBreakEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				if (! $event->getPlayer ()->isOp ()) {
					$event->setCancelled ( true );
					echo "***[BH] cancel block BREAK for player " . $event->getPlayer ()->getName () . " | " . $event->getPlayer ()->getAddress () . "\n";
				}
			}
		}
	}
	
	/**
	 *
	 * @param BlockPlaceEvent $event
	 *        	@priority MONITOR
	 */
	public function onBlockPlace(BlockPlaceEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->setup->getHomeWorldName () )) {
				if (! $event->getPlayer ()->isOp ()) {
					$event->setCancelled ( true );
					echo "***[BH] cancel block PLACE for player " . $event->getPlayer ()->getName () . " | " . $event->getPlayer ()->getAddress () . "\n";
				}
			}
		}
	}
	
	/**
	 *
	 * @param PlayerItemHeldEvent $event        	
	 */
	public function onItemOnHand(PlayerItemHeldEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				$this->getPlugin ()->controller->handleHiderSelectBlockType ( $event->getPlayer (), $event->getItem ()->getId () );
			}
		}
	}
	
	/**
	 *
	 * @param PlayerMoveEvent $event        	
	 */
	public function onPlayerMove(PlayerMoveEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				$this->getPlugin ()->controller->moveHiderBlock ( $event->getPlayer (), $event->getFrom (), $event->getTo () );
				
				if (!$event->getPlayer ()->isOp ()) {
					foreach ( $this->plugin->getArenaManager ()->playArenas as &$arena ) {
						if ($arena instanceof ArenaModel and $arena->insideArena ( $event->getPlayer ()->getPosition () )) {
							if (! isset ( $arena->joinedplayers [$event->getPlayer ()->getName ()] ) && ! isset ( $arena->hidders [$event->getPlayer ()->getName ()] ) && ! isset ( $arena->seekers [$event->getPlayer ()->getName ()] )) {
								$event->getPlayer ()->teleport ( $arena->lobby );
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 *
	 * @param PlayerKickEvent $event        	
	 */
	public function onPlayerKicked(PlayerKickEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				$this->getPlugin ()->controller->handlePlayerLeavethePlay ( $event->getPlayer () );
			}
		}
	}
	
	/**
	 *
	 * @param PlayerQuitEvent $event        	
	 */
	public function onPlayerQuit(PlayerQuitEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			$this->getPlugin ()->controller->handlePlayerLeavethePlay ( $event->getPlayer () );
		}
	}
	
	/**
	 *
	 * @param EntityDeathEvent $event        	
	 */
	public function onPlayerDeath(EntityDeathEvent $event) {
		if ($event->getEntity () instanceof Player) {
			$this->getPlugin ()->controller->handlePlayerDeath ( $event->getEntity (), $event );
		}
	}
	
	/**
	 *
	 * @param EntityDamageEvent $event        	
	 */
	public function onPlayerHurt(EntityDamageEvent $event) {
		if ($event instanceof EntityDamageByEntityEvent) {
			if ($event->getEntity () instanceof Player && $event->getDamager () instanceof Player) {
				if (strtolower ( $event->getEntity ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
					if ($this->getPlugin ()->controller->cancelPlayerGotHurtEvent ( $event->getEntity (), $event->getDamager () )) {
						$event->setCancelled ( true );
					} else {
						$event->setKnockBack ( 0.1 );
						if ($this->getPlugin ()->controller->handlePlayerGotHurt ( $event->getEntity (), $event->getDamager () )) {
							$event->setCancelled ( true );
						}
					}
				}
			}
		}
	}
	
	/**
	 *
	 * @param Position $tappos        	
	 * @param Player $player        	
	 */
	public function tapOnServerHomeSign(Position $tappos, Player $player) {
		$homeSignPos = $this->getPlugin ()->setup->getHomeWorldSignPos ();
		if (! empty ( $homeSignPos ) and ! empty ( $homeSignPos->x ) and ! empty ( $homeSignPos->y ) and ! empty ( $homeSignPos->z )) {
			if (round ( $tappos->x ) === round ( $homeSignPos->x ) && round ( $tappos->y ) === round ( $homeSignPos->y ) && round ( $tappos->z ) === round ( $homeSignPos->z )) {
				$this->getPlugin ()->controller->teleportPlayerToHomeWorld ( $player );
				return;
			}
		}
		
		$serverSignPos = $this->getPlugin ()->setup->getServerLobbySignPos ();
		if (! empty ( $serverSignPos ) and ! empty ( $serverSignPos->x ) and ! empty ( $serverSignPos->y ) and ! empty ( $serverSignPos->z )) {
			if (round ( $tappos->x ) === round ( $serverSignPos->x ) && round ( $tappos->y ) === round ( $serverSignPos->y ) && round ( $tappos->z ) === round ( $serverSignPos->z )) {
				$this->getPlugin ()->controller->teleportPlayerToLobbyWorld ( $player );
				return;
			}
		}
	}
}