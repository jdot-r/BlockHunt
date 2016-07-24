<?php

namespace mcg76\game\blockhunt\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\item\Item;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\arenas\ArenaModel;

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
/**
 * Scheduled PlayArenaGate OPEN | CLOSE
 */
class PlayArenaGate extends PluginTask {
	private $plugin;
	private $arena;
	private $action;
	public function __construct(BlockHuntPlugIn $plugin, ArenaModel $resetArena, $action) {
		$this->plugin = $plugin;
		$this->arena = $resetArena;
		$this->action = $action;
		parent::__construct ( $plugin );
	}
	
	public function onRun($ticks) {
		if ($this->arena->seekergate1 != null && $this->arena->seekergate2 != null) {
			if ($this->action==ArenaModel::ARENA_GATE_CLOSE) {
				$this->plugin->arenaManager->setGate($this->arena,$this->arena->seekergate1, $this->arena->seekergate2, Item::get(Item::FENCE,4)->getBlock ());
			}
			if ($this->action==ArenaModel::ARENA_GATE_OPEN) {
				$this->plugin->arenaManager->setGate($this->arena,$this->arena->seekergate1, $this->arena->seekergate2, Item::get(Item::AIR)->getBlock ());
			}
		} else {
			$this->getPlugIn()->getLogger()->error("[BH] seeker gate configuration error");
		}
	}
	
	public function onCancel() {
	}
	protected function getMsg($key) {
		return $this->plugin->messages->getMessageByKey ( $key );
	}
	protected function getController() {
		return $this->getPlugIn ()->controller;
	}
	protected function getPlugIn() {
		return $this->plugin;
	}
	protected function getSetup() {
		return $this->getPlugIn ()->setup;
	}
	protected function log($msg) {
		 $this->getPlugIn ()->getLogger ()->info ( $msg );
	}
}
