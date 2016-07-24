<?php

namespace mcg76\game\blockhunt\tasks;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\tile\Tile;
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\math\Vector3;
use pocketmine\Server;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\utils\LevelUtil;

/**
 * PlayArenaResetTask
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class PlayArenaResetTask extends PluginTask {
	private $plugin;
	private $arena;
	
	/**
	 *
	 * @param PrivatePlotPlugIn $plugin        	
	 */
	public function __construct(BlockHuntPlugIn $plugin, ArenaModel $arena) {
		$this->plugin = $plugin;
		$this->arena = $arena;
		parent::__construct ( $plugin );
	}
	
	/**
	 *
	 * @param
	 *        	$ticks
	 */
	public function onRun($ticks) {
		try {
			LevelUtil::resetArenaBlocks ( $this->plugin, $this->arena );
		} catch ( \Exception $e ) {
			$message = $e->getCode () . "|" . $e->getLine () . "|" . $e->getMessage () . "|" . $e->getTraceAsString ();
			$this->plugin->getLogger ()->error ( $message );
		}
	}
	public function onCancel() {
	}
}
