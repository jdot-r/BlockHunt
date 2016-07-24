<?php

namespace mcg76\game\blockhunt\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\event\Cancellable;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\arenas\ArenaModel;
use pocketmine\utils\TextFormat;
use pocketmine\level\sound\BatSound;
use pocketmine\level\sound\DoorSound;
use mcg76\game\blockhunt\BlockHuntGameKit;
use mcg76\game\blockhunt\BlockHuntController;
use pocketmine\level\particle\PortalParticle;
use mcg76\game\blockhunt\utils\PortalManager;

/**
 * PlayReleaseSeeker
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class PlayReleaseSeeker extends PluginTask {
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

		Server::getInstance ()->broadcastMessage ( TextFormat::YELLOW.$this->getMsg("bh.play.gateopen"), $this->arena->seekers);
		Server::getInstance ()->broadcastMessage ( TextFormat::YELLOW.$this->getMsg("bh.play.gateopen"), $this->arena->hidders);		
		foreach ($this->arena->hidders as $player) {
			$this->arena->level->addSound(new DoorSound($player->getPosition()),array($player));			
		}
		$this->arena->level->addSound(new DoorSound($this->arena->seekergate2),$this->arena->seekers);
		PortalManager::addParticles($this->arena->level, $this->arena->seekergate2, 200);		
		$arenaResetTask = new PlayArenaGate ( $this->plugin, $this->arena, ArenaModel::ARENA_GATE_OPEN );
		$this->plugin->getServer ()->getScheduler ()->scheduleDelayedTask ( $arenaResetTask, 10 );			
		$this->arena->reset_time = (empty($this->arena->reset_time) || $this->arena->reset_time === 0) ? 180 : $this->arena->reset_time;
		$wait_time = $this->arena->reset_time * $this->plugin->getServer ()->getTicksPerSecond ();
		$arenaResetTask = new PlayFinishTask ( $this->plugin, $this->arena );
		$this->plugin->getServer ()->getScheduler ()->scheduleDelayedTask ( $arenaResetTask, $wait_time );
		Server::getInstance ()->broadcastMessage (TextFormat::GRAY. $this->getMsg("bh.play.timeout")." [" .TextFormat::GOLD. $this->arena->reset_time . "] ".TextFormat::GRAY.$this->getMsg("bh.play.seconds"), $this->arena->seekers);
		Server::getInstance ()->broadcastMessage (TextFormat::GRAY. $this->getMsg("bh.play.timeout")." [" . TextFormat::GOLD.$this->arena->reset_time . "] ".TextFormat::GRAY.$this->getMsg("bh.play.seconds"), $this->arena->hidders);
		Server::getInstance ()->broadcastMessage ( TextFormat::YELLOW.$this->getMsg("bh.play.seekerreleased"), $this->arena->seekers);
		Server::getInstance ()->broadcastMessage ( TextFormat::YELLOW.$this->getMsg("bh.play.seekerreleased"), $this->arena->hidders);		
		foreach ( $this->plugin->getArenaManager()->playArenas as &$arena ) {
			if ($arena->name == $this->arena->name) {
				$arena->timeOutTask = $arenaResetTask; 
				$arena->playFinishTime = microtime(true) + ($this->arena->reset_time);
				break;
			}
		}
	}
	public function onCancel() {
		$this->cancelled = true;
		parent::onCancel ();
	}
	
	protected function getMsg($key) {
		return $this->plugin->messages->getMessageByKey ( $key );
	}
}
