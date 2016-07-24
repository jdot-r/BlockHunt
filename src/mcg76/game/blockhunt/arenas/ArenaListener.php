<?php

namespace mcg76\game\blockhunt\arenas;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\level\Position;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\event\player\PlayerEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector2;
use pocketmine\item\Item;
use pocketmine\Server;
use mcg76\game\blockhunt\itemcase\ItemCaseBuilder;
use pocketmine\event\entity\EntityLevelChangeEvent;
use mcg76\game\blockhunt\itemcase\ItemCaseModel;
use pocketmine\network\protocol\MovePlayerPacket;
use mcg76\game\blockhunt\BlockHuntPlugIn;

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
class ArenaListener implements Listener {
	public $plugin = null;
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
	}
	public function getPlugin() {
		return $this->plugin;
	}
	public function onPlayerInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer ();
		$block = $event->getBlock ();
		if ($player instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				$this->getPlugin ()->arenaManager->handleTapOnArenaSigns ( $player, $block );
			}
		}
	}
	
	/**
	 *
	 * @param BlockBreakEvent $event        	
	 */
	public function onBlockBreak(BlockBreakEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				if ($this->getPlugin ()->setupModeAction === ArenaManager::COMMAND_ARENA_POSITION || $this->getPlugin ()->setupModeAction == ArenaManager::COMMAND_ARENA_POSITION || $this->getPlugin ()->setupModeAction == ArenaManager::COMMAND_ARENA_SEEKER_DOOR) {
					$this->getPlugin ()->arenaManager->handleBlockBreakSelection ( $event->getPlayer (), $event->getBlock () );
				}
			}
		}
	}
	
	/**
	 * Watch sign change
	 *
	 * @param SignChangeEvent $event        	
	 */
	public function onSignChange(SignChangeEvent $event) {
		if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
			$player = $event->getPlayer ();
			$block = $event->getBlock ();
			$line1 = $event->getLine ( 0 );
			$line2 = $event->getLine ( 1 );
			$line3 = $event->getLine ( 2 );
			$line4 = $event->getLine ( 3 );
			
			if (! $event->getPlayer ()->isOp ()) {
				$event->getPlayer ()->sendMessage ( "[BH] You are not authorized to use this command." );
				$event->setCancelled(true);
			} else {			
				if ($line1 != null && $line1 === "[blockhunt]") {
					if ($line2 != null && $line2 === "join") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignJoin ( $player, $arenaName, $block );
					}
					if ($line2 != null && $line2 === "exit") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignExit ( $player, $arenaName, $block );
					}
					if ($line2 != null && $line2 === "exit2") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignExit2 ( $player, $arenaName, $block );
					}
					
					if ($line2 != null && $line2 === "stats") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignStats ( $player, $arenaName, $block );
					}
					if ($line2 != null && $line2 === "stats2") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignStats2 ( $player, $arenaName, $block );
					}
					
					if ($line2 != null && $line2 === "seeker") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignJoinSeeker ( $player, $arenaName, $block );
					}
					
					if ($line2 != null && $line2 === "seeker2") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignJoinSeeker2 ( $player, $arenaName, $block );
					}
					if ($line2 != null && $line2 === "hider") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignJoinHider ( $player, $arenaName, $block );
					}
					if ($line2 != null && $line2 === "hider2") {
						$arenaName = $line3;
						$this->getPlugin ()->arenaManager->handleSetSignJoinHider2 ( $player, $arenaName, $block );
					}
					
					if ($line2 != null && $line2 === "teleport") {
						if ($line3 != null && $line3 === "lobby") {
							$this->getPlugin ()->getConfig ()->set ( "server_lobby_sign_level", $event->getBlock ()->level->getName () );
							$this->getPlugin ()->getConfig ()->set ( "server_lobby_sign_x", $event->getBlock ()->x );
							$this->getPlugin ()->getConfig ()->set ( "server_lobby_sign_y", $event->getBlock ()->y );
							$this->getPlugin ()->getConfig ()->set ( "server_lobby_sign_z", $event->getBlock ()->z );
							$this->getPlugin ()->getConfig ()->save ();
							$player->sendMessage ( "[BH] Server lobby sign location set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
						}
						if ($line3 != null && $line3 === "home") {
							$this->getPlugin ()->getConfig ()->set ( "blockhunt_home_sign_level", $event->getBlock ()->level->getName () );
							$this->getPlugin ()->getConfig ()->set ( "blockhunt_home_sign_x", $event->getBlock ()->x );
							$this->getPlugin ()->getConfig ()->set ( "blockhunt_home_sign_y", $event->getBlock ()->y );
							$this->getPlugin ()->getConfig ()->set ( "blockhunt_home_sign_z", $event->getBlock ()->z );
							$player->sendMessage ( "[BH] Home sign location set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							$this->getPlugin ()->getConfig ()->save ();
						}
					}
					
					if ($line2 != null && $line2 === "stat") {
						if ($line3 != null && $line3 === "podium") {
							if ($line4 != null && $line4 === "diamond") {
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_diamond_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_diamond_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_diamond_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] podium DIAMOND sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
							if ($line4 != null && $line4 === "gold") {
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_gold_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_gold_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_gold_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] podium GOLD sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
							if ($line4 != null && $line4 === "silver") {
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_silver_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_silver_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_podium_silver_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] podium SILVER sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
						}
						
						if ($line3 != null && $line3 === "seeker") {
							if ($line4 != null && $line4 === "gold") {
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_gold_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_gold_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_gold_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] Seeker podium GOLD sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
							if ($line4 != null && $line4 === "silver") {
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_silver_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_silver_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_silver_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] Seeker podium SILVER sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
							if ($line4 != null && $line4 === "bronse") {
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_bronse_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_bronse_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_seeker_podium_bronse_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] Seeker podium BRONSE sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
						}
						
						if ($line3 != null && $line3 === "hider") {
							if ($line4 != null && $line4 === "gold") {
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_gold_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_gold_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_gold_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] Hider podium GOLD sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
							if ($line4 != null && $line4 === "silver") {
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_silver_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_silver_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_silver_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] Hider podium SILVER sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
							if ($line4 != null && $line4 === "bronse") {
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_bronse_x", $event->getBlock ()->x );
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_bronse_y", $event->getBlock ()->y );
								$this->getPlugin ()->getConfig ()->set ( "bh_hider_podium_bronse_z", $event->getBlock ()->z );
								$this->getPlugin ()->getConfig ()->save ();
								$player->sendMessage ( "[BH] Hider podium BRONSE sign set to " . $event->getBlock ()->x . " " . $event->getBlock ()->y . " " . $event->getBlock ()->z );
							}
						}
					}
				}
			}
		}
	}
}