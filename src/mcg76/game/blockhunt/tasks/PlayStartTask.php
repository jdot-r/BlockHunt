<?php

namespace mcg76\game\blockhunt\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\event\Cancellable;
use mcg76\game\blockhunt\BlockHuntPlugIn;

/**
 * Handle Game Round timeout
 * 
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 * 
 * @author MCG76
 *
 */
class PlayStartTask extends PluginTask {
	private $plugin;
	private $cancelled = false;	
	
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
		parent::__construct ( $plugin );
	}
	
	public function onRun($ticks) {
		if ($this->cancelled) {
			return;		
		}		
		$this->plugin->game_mode = 99;
	}
	
	public function onCancel() {
		$this->cancelled = true;	
		parent::onCancel();
	}
}
