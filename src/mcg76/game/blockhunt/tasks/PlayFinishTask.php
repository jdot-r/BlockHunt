<?php

namespace mcg76\game\blockhunt\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\event\Cancellable;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\BlockHuntGameKit;

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
class PlayFinishTask extends PluginTask {
	private $plugin;
	private $cancelled = false;
	private $arena;
	public function __construct(BlockHuntPlugIn $plugin, ArenaModel $arena) {
		$this->plugin = $plugin;
		$this->arena = $arena;
		parent::__construct ( $plugin );
	}
	public function onRun($ticks) {
		if ($this->cancelled) {
			return;
		}		
		$this->plugin->controller->announceArenaGameFinish ( $this->arena );
	}
	public function onCancel() {
		$this->cancelled = true;
		parent::onCancel ();
	}
}
