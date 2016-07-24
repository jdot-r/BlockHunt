<?php

namespace mcg76\game\blockhunt\defence;

use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Player;
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
class DefenceListener implements Listener {
	public $plugin = null;
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
	}
	public function getPlugin() {
		return $this->plugin;
	}
	
	/**
	 *
	 * @param BlockBreakEvent $event        	
	 */
	public function onBlockBreak(BlockBreakEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->getLevel ()->getName () ) === strtolower ( $this->getPlugin ()->homeLevelName )) {
				if ($this->getPlugin ()->setupModeAction === DefenceManager::COMMAND_DEFENCE_POSITION) {
					$this->getPlugin ()->defenceManager->handleBlockBreakSelection ( $event->getPlayer (), $event->getBlock () );
				}
			}
		}
	}
}