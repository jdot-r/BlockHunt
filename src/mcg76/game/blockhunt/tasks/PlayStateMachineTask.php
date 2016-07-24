<?php

namespace mcg76\game\blockhunt\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\arenas\ArenaManager;
use mcg76\game\blockhunt\BlockHuntGameKit;
use pocketmine\utils\TextFormat;
use mcg76\game\blockhunt\BlockHuntController;
use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\BatSound;
use pocketmine\level\sound\ClickSound;
use mcg76\game\blockhunt\utils\SignHelper;
use pocketmine\level\Level;
use mcg76\game\blockhunt\utils\PortalManager;
use mcg76\game\blockhunt\utils\LevelUtil;

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
 * PlayStateMachineTask
 */
class PlayStateMachineTask extends PluginTask {
	private $plugin;
	
	/**
	 *
	 * @param BlockHuntPlugIn $plugin        	
	 */
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
		parent::__construct ( $plugin );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\scheduler\Task::onRun()
	 */
	public function onRun($ticks) {
		foreach ( $this->getPlugIn ()->getArenaManager ()->playArenas as &$arena ) {
			if ($arena instanceof ArenaModel) {
				if (! $arena->active) {
					continue;
				}
				if ($this->plugin->forceReset) {
					$gateTask = new PlayArenaGate ( $this->plugin, $arena, ArenaModel::ARENA_GATE_CLOSE );
					$this->plugin->getServer ()->getScheduler ()->scheduleDelayedTask ( $gateTask, 10 );
					$this->plugin->forceReset = false;
				}
				$this->updateSigns ( $this->getPlugIn ()->getHomeLevel (), $arena );
				$this->updatePodiumSigns ( $this->plugin->homeLevel );
				$this->updateWorldSpawnParticles ( $arena );
				$arena->min = empty ( $arena->min ) ? 2 : $arena->min;
				$arena->max = empty ( $arena->max ) ? 60 : $arena->max;
				if (count ( $arena->joinedplayers ) > 0) {
					if ($arena->count_down != 0 && $arena->status === ArenaModel::ARENA_STATUS_AVAILABLE) {
						$hiderCount = 0;
						$seekerCount = 0;
						$randomPickCount = 0;
						foreach ( $arena->joinedplayers as $player => $role ) {
							if ($role === ArenaManager::PLAYER_ROLE_SEEKER) {
								$seekerCount ++;
							} elseif ($role === ArenaManager::PLAYER_ROLE_HIDER) {
								$hiderCount ++;
							} elseif ($role === ArenaManager::PLAYER_ROLE_RANDOM) {
								$randomPickCount ++;
							}
						}
						$gamejoinedplayers = [ ];
						if (($hiderCount + $seekerCount) >= $arena->min || ($randomPickCount >= $arena->min)) {
							// make sure there is seeker and hider
							if ($hiderCount > 0 && $seekerCount > 0 || $randomPickCount > 0) {
								foreach ( $arena->joinedplayers as $player => $role ) {
									$p = Server::getInstance ()->getPlayerExact ( $player );
									$gamejoinedplayers [] = $p;
								}
								$message = TextFormat::GRAY . $this->getMsg ( "bh.play.start.countdown" ) . TextFormat::GREEN . $arena->count_down . TextFormat::GRAY . $this->getMsg ( "bh.play.start.seconds" );
								BlockHuntController::broadcastPopUpTips ( $message, $gamejoinedplayers );
								if ($arena->count_down > 0) {
									$arena->count_down --;
								}
								$autoresetcount = empty ( $arena->resetCountDown ) ? 5 : $arena->resetCountDown;
								if ($arena->count_down === $autoresetcount) {
									$autoreset = empty ( $arena->resetNewGame ) ? false : $arena->resetNewGame;
									if ($autoreset) {
										$arenaResetTask = new PlayArenaResetTask ( $this->plugin, $arena );
										$this->getPlugIn ()->getServer ()->getScheduler ()->scheduleDelayedTask ( $arenaResetTask, 3 );
									}
								}
								if ($arena->count_down === 2) {
									$arenaResetTask = new PlayArenaGate ( $this->plugin, $arena, ArenaModel::ARENA_GATE_CLOSE );
									$this->getPlugIn ()->getServer ()->getScheduler ()->scheduleDelayedTask ( $arenaResetTask, 5 );
								}
								continue;
							}
						}
						if ($hiderCount === 0) {
							foreach ( $arena->joinedplayers as $player => $role ) {
								$p = Server::getInstance ()->getPlayerExact ( $player );
								$gamejoinedplayers [] = $p;
							}
							$message = TextFormat::GRAY . $this->getMsg ( "bh.play.start.missinghiders" );
							BlockHuntController::broadcastPopUpTips ( $message, $gamejoinedplayers );
						}
						if ($seekerCount === 0) {
							foreach ( $arena->joinedplayers as $player => $role ) {
								$p = Server::getInstance ()->getPlayerExact ( $player );
								$gamejoinedplayers [] = $p;
							}
							$message = TextFormat::GRAY . $this->getMsg ( "bh.play.start.missingseekers" );
							BlockHuntController::broadcastPopUpTips ( $message, $gamejoinedplayers );
						}
						if ($randomPickCount === 0) {
							foreach ( $arena->joinedplayers as $player => $role ) {
								$p = Server::getInstance ()->getPlayerExact ( $player );
								$gamejoinedplayers [] = $p;
							}
							$message = TextFormat::GRAY . "[BH] not enought players to start [min." . $arena->min . "]";
							BlockHuntController::broadcastPopUpTips ( $message, $gamejoinedplayers );
						}
					} elseif ($arena->count_down === 0 && $arena->status === ArenaModel::ARENA_STATUS_AVAILABLE) {
						$arena->status = "preparing";
						foreach ( $arena->joinedplayers as $player => $role ) {
							$p = Server::getInstance ()->getPlayerExact ( $player );
							if ($p != null) {
								if ($role == ArenaManager::PLAYER_ROLE_SEEKER) {
									$arena->seekers [$player] = $p;
								} elseif ($role == ArenaManager::PLAYER_ROLE_HIDER) {
									$arena->hidders [$player] = $p;
								} elseif ($role == ArenaManager::PLAYER_ROLE_RANDOM) {
									if (count ( $arena->seekers ) === 0) {
										$arena->seekers [$player] = $p;
										$p->teleport ( $arena->seeker_warp );
									} elseif (count ( $arena->hidders ) === 0) {
										$arena->hidders [$player] = $p;
										$p->teleport ( $arena->hider_warp );
									} else {
										if (rand ( 1, 2 ) === 1) {
											$arena->seekers [$player] = $p;
											$p->teleport ( $arena->seeker_warp );
										} else {
											$arena->hidders [$player] = $p;
											$p->teleport ( $arena->hider_warp );
										}
									}
								}
							}
						}
						foreach ( $arena->seekers as $player ) {
							if ($player instanceof Player) {
								foreach ( $arena->hidders as $hider ) {
									$player->hidePlayer ( $hider );
								}
								$this->getPlugIn ()->gameKit->putOnGameKit ( $player, BlockHuntGameKit::KIT_SEEKER );
								if (! $player->getInventory ()->contains ( Item::get ( Item::COMPASS ) )) {
									$player->getInventory ()->setItem ( 1, Item::get ( Item::COMPASS ) );
								}
							}
							$arena->seekersoriginal [$player->getName ()] = $player;
						}
						$seekerReleaseTime = $arena->seekerReleaseTime;
						$wait_time = $seekerReleaseTime * $this->getPlugIn ()->getServer ()->getTicksPerSecond ();
						$arenaResetTask = new PlayReleaseSeeker ( $this->getPlugIn (), $arena );
						$this->getPlugIn ()->getServer ()->getScheduler ()->scheduleDelayedTask ( $arenaResetTask, $wait_time );
						// announce delay
						$message = TextFormat::GRAY . $this->getMsg ( "bh.play.start.releaseseekers" ) . " [" . TextFormat::GOLD . $seekerReleaseTime . "] " . TextFormat::GRAY . $this->getMsg ( "bh.play.start.seconds" );
						Server::getInstance ()->broadcastMessage ( $message, $arena->seekers );
						Server::getInstance ()->broadcastMessage ( $message, $arena->hidders );
						foreach ( $arena->hidders as $player ) {
							if ($player instanceof Player) {
								if (! empty ( $player->getInventory () )) {
									$player->getInventory ()->clearAll ();
								}
								$this->getPlugIn ()->gameKit->putOnGameKit ( $player, BlockHuntGameKit::KIT_HIDER );
								foreach ( $arena->blocks as $blockId => $bname ) {
									$player->getInventory ()->addItem ( new Item ( $blockId, 0, 1 ) );
								}
								$selectId = array_rand ( $arena->blocks );
								$selectName = $arena->blocks [$selectId];
								$arena->hiderblocks [$player->getName ()] = $selectId;
								// notify player
								$player->sendMessage ( TextFormat::GRAY . $this->getMsg ( "bh.play.start.assignedblock" ) . " [" . TextFormat::GREEN . $selectName . TextFormat::GRAY . "]" );
								$player->sendMessage ( TextFormat::DARK_AQUA . $this->getMsg ( "bh.play.start.changeblock" ) );
								$player->getLevel ()->addSound ( new ClickSound ( $player->getPosition () ), array (
										$player 
								) );
								$player->getLevel ()->addSound ( new ClickSound ( $player->getPosition () ), array (
										$player 
								) );
								$player->getLevel ()->updateAllLight ( $player->getPosition () );
								$player->getLevel ()->updateAround ( $player->getPosition () );
							}
						}
						$arena->status = ArenaModel::ARENA_STATUS_PLAYING;
						$arena->playStartTime = microtime ( true );
						$arena->playFinishTime = $arena->playStartTime + $arena->reset_time;
					}
				}
				
				if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
					foreach ( $arena->seekers as $player ) {
						foreach ( $arena->hidders as $hider ) {
							if ($player->canSee ( $hider )) {
								$player->hidePlayer ( $hider );
							}
							if ($hider instanceof Player) {
								// check if hider block exist
								if (isset ( $arena->hiderblocks [$hider->getName ()] )) {
									$hid = $arena->hiderblocks [$hider->getName ()];
									$envid = $arena->level->getBlockIdAt ( $hider->x, $hider->y, $hider->z );
									if ($envid === Item::AIR || $envid === Item::TALL_GRASS) {
										$hider->sendTip ( TextFormat::GRAY . "[BH] solidify hider block [" . $hid . "]" );
										$arena->level->setBlock ( $hider->getPosition (), Item::get ( $hid )->getBlock (), false, false );
									}
								}
							}
						}
						foreach ( $arena->seekers as $seeker ) {
							if (! $player->canSee ( $seeker )) {
								$player->showPlayer ( $seeker );
							}
						}
						if (! empty ( $player->getInventory () )) {
							$armors = $player->getInventory ()->getArmorContents ();
							if (empty ( $armors ) || count ( $armors ) === 0 || ($armors [0]->getId () === 0 && $armors [1]->getId () === 0 && $armors [2]->getId () === 0 && $armors [3]->getId () === 0)) {
								$this->getPlugIn ()->gameKit->putOnGameKit ( $player, BlockHuntGameKit::KIT_SEEKER );
							}
						}
					}
					$this->remiderPlayerOnTimeLeft ( $arena );
					if (count ( $arena->hidders ) === 0 || count ( $arena->seekers ) === 0) {
						if ($arena->timeOutTask != null) {
							$arena->timeOutTask->onCancel ();
							$this->plugin->getServer ()->getScheduler ()->cancelTask ( $arena->timeOutTask->getTaskId () );
							$arena->timeOutTask = null;
						}
						$this->plugin->controller->announceArenaGameFinish ( $arena );
					}
				}
			}
		}
		unset ( $arena );
	}
	
	/**
	 *
	 * @param ArenaModel $arena        	
	 */
	public function remiderPlayerOnTimeLeft(ArenaModel $arena) {
		// check elapse time
		$nowtime = microtime ( true );
		$timediff = $nowtime - $arena->playStartTime;
		$timeRemains = round ( $arena->playFinishTime - $nowtime );
		
		if ($arena->reminderCountDown === 0) {
			$message = TextFormat::GRAY . $this->getMsg ( "bh.play.elapsed.time" ) . TextFormat::GOLD . round ( $timediff ) . TextFormat::GRAY . $this->getMsg ( "bh.play.elapsed.hider" ) . TextFormat::GOLD . count ( $arena->hidders ) . TextFormat::GRAY . $this->getMsg ( "bh.play.elapsed.seeker" ) . TextFormat::GOLD . count ( $arena->seekers );
			Server::getInstance ()->broadcastMessage ( $message, $arena->seekers );
			Server::getInstance ()->broadcastMessage ( $message, $arena->hidders );
			$arena->reminderCountDown = 3;
		} else {
			if ($arena->reminderCountDown > 0) {
				$arena->reminderCountDown --;
			}
		}
		
		for($i = 0; $i < 22; $i ++) {
			$message = TextFormat::GREEN . "BH " . TextFormat::GRAY . "|" . " SK [" . TextFormat::BLUE . count ( $arena->seekers ) . TextFormat::GRAY . "] HD [" . TextFormat::AQUA . count ( $arena->hidders ) . TextFormat::GRAY . "]" . " Elapsed [" . TextFormat::WHITE . round ( $timediff, 0 ) . TextFormat::GRAY . "s] | Remains [" . TextFormat::GOLD . round ( $timeRemains, 0 ) . TextFormat::GRAY . "s]";
			BlockHuntController::broadcastPopUpText ( TextFormat::GRAY . $message, $arena->seekers );
			BlockHuntController::broadcastPopUpText ( TextFormat::GRAY . $message, $arena->hidders );
		}
		
		// reminder
		if ($timeRemains < 10) {
			$message = TextFormat::GRAY . "[BH] time left [" . TextFormat::RED . $timeRemains . TextFormat::GRAY . "]" . count ( $arena->hidders ) . " | " . $this->getMsg ( "bh.play.seeker" ) . count ( $arena->seekers );
			BlockHuntController::broadcastPopUpTips ( $message, $arena->seekers );
			BlockHuntController::broadcastPopUpTips ( $message, $arena->hidders );
		}
	}
	
	/**
	 *
	 * @param Level $level        	
	 * @param ArenaModel $arena        	
	 */
	private function updateSigns($level, ArenaModel $arena) {
		$i = mt_rand ( 1, 10 );
		if ($i < 5) {
			return;
		}
		if (is_null ( $level )) {
			$level = $this->plugin->getHomeLevel ();
			if ($level === null) {
				$level = $arena->level;
			}
		}
		$seekers = 0;
		$hider = 0;
		$joined = 0;
		foreach ( $arena->joinedplayers as $player => $role ) {
			if ($role == ArenaManager::PLAYER_ROLE_SEEKER) {
				$seekers ++;
			} elseif ($role == ArenaManager::PLAYER_ROLE_HIDER) {
				$hider ++;
			} elseif ($role == ArenaManager::PLAYER_ROLE_RANDOM) {
				$joined ++;
			}
		}
		
		if (! empty ( $arena->signJoin )) {
			$sign = $level->getTile ( $arena->signJoin );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $this->getMsg ( "bh.play.signs.title" ), TextFormat::BLUE . "Join Auto-Role", TextFormat::GRAY . count ( $arena->joinedplayers ) . "/m." . $arena->min . " | " . TextFormat::GRAY . count ( $arena->joinedplayers ) . "/x." . $arena->max, TextFormat::DARK_GREEN . $this->getDisplayStatus ( $arena->status ) );
			}
		}
		
		if (! empty ( $arena->signJoinSeeker )) {
			$sign = $level->getTile ( $arena->signJoinSeeker );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $this->getMsg ( "bh.play.signs.title" ), TextFormat::LIGHT_PURPLE . $this->getMsg ( "bh.play.signs.join.seekers" ), TextFormat::GRAY . count ( $arena->seekers ) . "/m." . $arena->min . " | " . TextFormat::GRAY . count ( $arena->joinedplayers ) . "/x." . $arena->allowSeekers, TextFormat::DARK_GREEN . $this->getDisplayStatus ( $arena->status ) );
			}
		}
		
		if (! empty ( $arena->signJoinSeeker2 )) {
			$sign = $level->getTile ( $arena->signJoinSeeker2 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $this->getMsg ( "bh.play.signs.title" ), TextFormat::LIGHT_PURPLE . $this->getMsg ( "bh.play.signs.join.seekers" ), TextFormat::GRAY . $arena->name, TextFormat::DARK_GREEN . $this->getDisplayStatus ( $arena->status ) );
			}
		}
		
		if (! empty ( $arena->signJoinHider )) {
			$sign = $level->getTile ( $arena->signJoinHider );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $this->getMsg ( "bh.play.signs.title" ), TextFormat::AQUA . $this->getMsg ( "bh.play.signs.join.hiders" ), TextFormat::GRAY . TextFormat::GRAY . count ( $arena->joinedplayers ) . "/m." . $arena->min . " | " . TextFormat::GRAY . count ( $arena->joinedplayers ) . "/x." . $arena->allowHiders, TextFormat::DARK_GREEN . $this->getDisplayStatus ( $arena->status ) );
			}
		}
		
		if (! empty ( $arena->signJoinHider2 )) {
			$sign = $level->getTile ( $arena->signJoinHider2 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $this->getMsg ( "bh.play.signs.title" ), TextFormat::AQUA . $this->getMsg ( "bh.play.signs.join.hiders" ), TextFormat::GRAY . $arena->name, TextFormat::DARK_GREEN . $this->getDisplayStatus ( $arena->status ) );
			}
		}
		
		if (! empty ( $arena->signStats )) {
			$sign = $level->getTile ( $arena->signStats );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $arena->name, TextFormat::GRAY . $this->getMsg ( "bh.play.signs.stats.min" ) . TextFormat::GRAY . $arena->min . TextFormat::GRAY . $this->getMsg ( "bh.play.signs.stats.joined" ) . TextFormat::GOLD . count ( $arena->joinedplayers ), (TextFormat::GRAY . $this->getMsg ( "bh.play.signs.stats.seekers2" ) . TextFormat::GOLD . $seekers . TextFormat::GRAY . "/" . $arena->allowSeekers), TextFormat::GRAY . ($this->getMsg ( "bh.play.signs.stats.hiders2" ) . TextFormat::GOLD . $hider . TextFormat::GRAY . "/" . $arena->allowHiders) );
			}
		}
		
		if (! empty ( $arena->signStats2 )) {
			$sign = $level->getTile ( $arena->signStats2 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $arena->name, TextFormat::GRAY . $this->getMsg ( "bh.play.signs.stats.min" ) . TextFormat::GRAY . $arena->min . TextFormat::GRAY . $this->getMsg ( "bh.play.signs.stats.joined" ) . TextFormat::GOLD . count ( $arena->joinedplayers ), (TextFormat::GRAY . $this->getMsg ( "bh.play.signs.stats.seekers2" ) . TextFormat::GOLD . $seekers . TextFormat::GRAY . "/" . $arena->allowSeekers), TextFormat::GRAY . ($this->getMsg ( "bh.play.signs.stats.hiders2" ) . TextFormat::GOLD . $hider . TextFormat::GRAY . "/" . $arena->allowHiders) );
			}
		}
		
		if (! empty ( $arena->signExit )) {
			$sign = $level->getTile ( $arena->signExit );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $arena->name, " ", TextFormat::RED . "EXIT", " " );
			}
		}
		if (! empty ( $arena->signExit2 )) {
			$sign = $level->getTile ( $arena->signExit2 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::WHITE . $arena->name, " ", TextFormat::RED . "EXIT", " " );
			}
		}
	}
	private function updatePodiumSigns($level) {
		$i = mt_rand ( 1, 10 );
		if ($i > 5) {
			return;
		}
		SignHelper::updateHallOfFrameWinners ( $this->plugin );
		SignHelper::updateHallOfFrameWinnersHider ( $this->plugin );
		SignHelper::updateHallOfFrameWinnersSeeker ( $this->plugin );
	}
	private function updateWorldSpawnParticles(ArenaModel $arena) {
		$i = mt_rand ( 1, 10 );
		if ($i > 5) {
			return;
		}
		PortalManager::addParticles ( $arena->level, new Position ( $arena->entrance->x, $arena->entrance->y + 2, $arena->entrance->z, $arena->level ), 50 );
	}
	
	/**
	 *
	 * @param ArenaModel $arenastatus        	
	 * @return string
	 */
	private function getDisplayStatus($arenastatus) {
		if ($arenastatus == ArenaModel::ARENA_STATUS_AVAILABLE) {
			return $this->getMsg ( "bh.play.arena.available" );
		}
		if ($arenastatus == ArenaModel::ARENA_STATUS_WAITING) {
			return $this->getMsg ( "bh.play.arena.waiting" );
		}
		if ($arenastatus == ArenaModel::ARENA_STATUS_PLAYING) {
			return $this->getMsg ( "bh.play.arena.playing" );
		}
		if ($arenastatus == ArenaModel::ARENA_STATUS_COUNT_DOWN) {
			return $this->getMsg ( "bh.play.arena.countdown" );
		}
		return $arenastatus;
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
	protected function getBuilder() {
		return $this->getPlugIn ()->builder;
	}
	protected function log($msg) {
		$this->getPlugIn ()->getLogger ()->info ( $msg );
	}
}
