<?php

namespace mcg76\game\blockhunt;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use mcg76\game\blockhunt\arenas\ArenaModel;
use pocketmine\Server;
use mcg76\game\blockhunt\defence\DefenceModel;
use mcg76\game\blockhunt\tasks\PlayArenaGate;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\PortalParticle;
use mcg76\game\blockhunt\utils\PortalManager;
use pocketmine\utils\TextFormat;
use pocketmine\network\protocol\TextPacket;
use pocketmine\network\Network;
use mcg76\game\blockhunt\arenas\ArenaManager;
use pocketmine\level\sound\PopSound;
use mcg76\game\blockhunt\arenas\ArenaBlock;

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
class BlockHuntController extends MiniGameBase {
	const BH_PERMISSION_PLAY = "mcg76.blockhunt";
	public function __construct(BlockHuntPlugIn $plugin) {
		parent::__construct ( $plugin );
	}
	
	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @staticvar Singleton $instance The *Singleton* instances of this class.
	 *           
	 * @return Singleton The *Singleton* instance.
	 */
	public static function getInstance(BlockHuntPlugIn $plugin) {
		static $instance = null;
		if (null === $instance) {
			$instance = new BlockHuntController ( $plugin );
		}
		return $instance;
	}
	
	/**
	 *
	 * @param ArenaModel $arena        	
	 */
	public function announceArenaGameFinish(ArenaModel $arena) {
		Server::getInstance ()->broadcastMessage ( TextFormat::YELLOW . $this->getMsg ( "bh.play.finished.message" ), $arena->seekers );
		Server::getInstance ()->broadcastMessage ( TextFormat::YELLOW . $this->getMsg ( "bh.play.finished.message" ), $arena->hidders );
		
		$coins = empty ( $arena->reward ) ? 5 : $arena->reward;
		
		if (count ( $arena->hidders ) > 0) {
			foreach ( $arena->hidders as $player ) {
				if ($coins > 0) {
					Server::getInstance ()->broadcastMessage ( TextFormat::AQUA . $this->getMsg ( "bh.play.finished.hinderwin" ) . TextFormat::GOLD . $player->getName () . TextFormat::WHITE . $this->getMsg ( "bh.play.won" ) . TextFormat::GOLD . $coins . " " . TextFormat::WHITE . $this->getMsg ( "bh.play.coins" ), $arena->seekers );
					Server::getInstance ()->broadcastMessage ( TextFormat::AQUA . $this->getMsg ( "bh.play.finished.hinderwin" ) . TextFormat::GOLD . $player->getName () . TextFormat::WHITE . $this->getMsg ( "bh.play.won" ) . TextFormat::GOLD . $coins . " " . TextFormat::WHITE . $this->getMsg ( "bh.play.coins" ), $arena->hidders );
				} else {
					Server::getInstance ()->broadcastMessage ( TextFormat::AQUA . $this->getMsg ( "bh.play.finished.hinderwin" ) . TextFormat::GOLD . $player->getName (), $arena->seekers );
					Server::getInstance ()->broadcastMessage ( TextFormat::AQUA . $this->getMsg ( "bh.play.finished.hinderwin" ) . TextFormat::GOLD . $player->getName (), $arena->hidders );
				}
				$this->plugin->profileprovider->upsetPlayerWinning ( $player->getName (), $coins );
				$this->plugin->profileprovider->addPlayerWinningHider ( $player->getName () );
			}
			foreach ( $arena->seekers as $player ) {
				$this->plugin->profileprovider->upsetPlayerLoss ( $player->getName () );
				$this->plugin->profileprovider->addPlayerLossSeeker ( $player->getName () );
			}
		} elseif (count ( $arena->hidders ) === 0) {
			foreach ( $arena->seekers as $player ) {
				if (isset ( $arena->seekersoriginal [$player->getName ()] )) {
					if ($coins > 0) {
						Server::getInstance ()->broadcastMessage ( TextFormat::BLUE . $this->getMsg ( "bh.play.finished.seekerwin" ) . TextFormat::GOLD . $player->getName () . TextFormat::WHITE . $this->getMsg ( "bh.play.won" ) . TextFormat::GOLD . $coins . " " . TextFormat::WHITE . $this->getMsg ( "bh.play.coins" ), $arena->seekers );
						Server::getInstance ()->broadcastMessage ( TextFormat::BLUE . $this->getMsg ( "bh.play.finished.seekerwin" ) . TextFormat::GOLD . $player->getName () . TextFormat::WHITE . $this->getMsg ( "bh.play.won" ) . TextFormat::GOLD . $coins . " " . TextFormat::WHITE . $this->getMsg ( "bh.play.won" ), $arena->hidders );
					} else {
						Server::getInstance ()->broadcastMessage ( TextFormat::AQUA . $this->getMsg ( "bh.play.finished.hinderwin" ) . TextFormat::GOLD . $player->getName (), $coins, $arena->seekers );
						Server::getInstance ()->broadcastMessage ( TextFormat::AQUA . $this->getMsg ( "bh.play.finished.hinderwin" ) . TextFormat::GOLD . $player->getName (), $arena->hidders );
					}
					$this->plugin->profileprovider->upsetPlayerWinning ( $player->getName (), $coins );
					$this->plugin->profileprovider->addPlayerWinningSeeker ( $player->getName () );
				} else {
					// record a loss
					$player->sendMessage ( TextFormat::YELLOW . "[BH] try again?" );
				}
			}
			foreach ( $arena->hidders as $player ) {
				$this->plugin->profileprovider->upsetPlayerLoss ( $player->getName () );
				$this->plugin->profileprovider->addPlayerLossHider ( $player->getName () );
			}
		}
		
		foreach ( $arena->seekers as $player ) {
			BlockHuntGameKit::removePlayerAllInventories ( $player );
			
			foreach ( $arena->hidders as $hider ) {
				$player->showPlayer ( $hider );
			}
			// notify
			$player->getLevel ()->addSound ( new PopSound ( $player->getPosition () ), array (
					$player 
			) );
			$player->getLevel ()->addSound ( new PopSound ( $player->getPosition () ), array (
					$player 
			) );
			unset ( $arena->seekers [$player->getName ()] );
			$player->teleport ( new Vector3 ( $arena->lobby->x, $arena->lobby->y, $arena->lobby->z ) );
		}
		
		foreach ( $arena->hidders as $player ) {
			BlockHuntGameKit::removePlayerAllInventories ( $player );
			$this->cleanUpHiderTrailer ( $player );
			// unhide seekers too
			foreach ( $arena->seekers as $seeker ) {
				$player->showPlayer ( $seeker );
			}
			// notify
			$player->getLevel ()->addSound ( new PopSound ( $player->getPosition () ), array (
					$player 
			) );
			$player->getLevel ()->addSound ( new PopSound ( $player->getPosition () ), array (
					$player 
			) );
			unset ( $arena->hiderblocks [$player->getName ()] );
			unset ( $arena->hidders [$player->getName ()] );
			$player->teleport ( new Vector3 ( $arena->lobby->x, $arena->lobby->y, $arena->lobby->z ) );
		}
		foreach ( $this->plugin->getArenaManager ()->playArenas as &$aa ) {
			if ($aa->name == $arena->name) {
				$aa->seekers = [ ];
				$aa->hidders = [ ];
				$aa->joinedplayers = [ ];
				$aa->hiderblocks = [ ];
				$aa->count_down = 5;
				$aa->status = ArenaModel::ARENA_STATUS_AVAILABLE;
			}
		}
		$arenaResetTask = new PlayArenaGate ( $this->plugin, $arena, ArenaModel::ARENA_GATE_CLOSE );
		$this->plugin->getServer ()->getScheduler ()->scheduleDelayedTask ( $arenaResetTask, 100 );
		
		unset ( $arena->seekersoriginal );
		$arena->seekersoriginal = [ ];
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param Position $to        	
	 * @return boolean
	 */
	public function cancelPlayerMovement(Player $player, Position $to) {
		$isPlayerInGame = false;
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			if (isset ( $arena->hidders [$player->getName ()] )) {
				$isPlayerInGame = true;
				if (! $arena->contains ( $to )) {
					$player->sendMessage ( $this->getMsg ( "bh.play.warning.border" ) );
					return true;
					break;
				}
			}
			if (isset ( $arena->seekers [$player->getName ()] )) {
				$isPlayerInGame = true;
				if (! $arena->contains ( $to )) {
					$player->sendMessage ( $this->getMsg ( "bh.play.warning.border" ) );
					return true;
					break;
				}
			}
			if ($arena->contains ( $to )) {
				if (! $isPlayerInGame) {
					if ($arena->status == ArenaModel::ARENA_STATUS_PLAYING) {
						$this->teleportPlayerToHomeWorld ( $player );
					}
				} else {
					if ($arena->status == ArenaModel::ARENA_STATUS_PLAYING) {
						// $this->getLog ()->info ( $player->getName()." onGround = " . $player->onGround );
						// $this->getLog ()->info ( $player->getName()." moveFlying = " . $player->moveFlying() );
						// $this->getLog ()->info ( $player->getName()." fall distance = " . $player->fallDistance );
						// $blockid = $player->getLevel()->getBlockIdAt($player->x, $player->y-1, $player->z);
						// if ($blockid==0 && $player->fallDistance < 1) {
						// $player->sendMessage ( "[BH] You are not allow to fly!" );
						// return true;
						// break;
						// }
					}
				}
			}
		}
		return false;
	}
	public function handleHiderSelectBlockType(Player $player, $newBlockID) {
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			if ($arena instanceof ArenaModel) {
				if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
					foreach ( $arena->hidders as $hider ) {
						if ($hider instanceof Player) {
							if (isset ( $arena->hidders [$player->getName ()] )) {
								if (isset ( $arena->blocks [$newBlockID] )) {
									if (isset ( $arena->hiderblocks [$player->getName ()] )) {
										$pbid = $arena->hiderblocks [$player->getName ()];
										$ebid = $player->getLevel ()->getBlockIdAt ( $player->x, $player->y, $player->z );
										if ($ebid === $pbid) {
											self::removeBlock ( $player, $player->getPosition () );
										}
									}
									$arena->hiderblocks [$player->getName ()] = $newBlockID;
									$message = TextFormat::GRAY . "[BH] new block selected [" . TextFormat::AQUA . $newBlockID . TextFormat::GRAY . "]";
									$player->sendMessage ( $message );
									break;
								}
							}
						}
					}
					foreach ( $arena->seekers as $seeker ) {
						if ($seeker instanceof Player) {
							if ($player->getInventory ()->getItemInHand () === Item::COMPASS) {
								$hiders = [ ];
								foreach ( $arena->hidders as $h ) {
									$dif = $player->distance ( $h->getPosition () );
									$hiders [$h->getName ()] = $dif;
								}
								if (count ( $hiders ) > 0) {
									asort ( $hiders );
								}
								foreach ( $hiders as $hd ) {
									$player->sendMessage ( "[BH] nearby hiders distrance " . $hd );
								}
							}
						}
					}
				}
			}
		}
	}
	public function handlePlayerRejoinThePlay(Player $player) {
		$playerInGame = false;
		$lobby = null;
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			$lobby = $arena->lobby;
			if (isset ( $arena->seekers [$player->getName ()] )) {
				if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
					if (! is_null ( $arena->seeker_warp )) {
						$player->sendMessage ( TextFormat::BLUE . $this->getMsg ( "bh.play.teleport.seekerswarp" ) );
						$playerInGame = true;
						$this->getPlugIn ()->gameKit->putOnGameKit ( $player, BlockHuntGameKit::KIT_SEEKER );
						$player->teleport ( $arena->seeker_warp );
						return;
						break;
					}
				}
			}
		}
		
		if (! $playerInGame) {
			if ($this->plugin->getConfig ()->get ( "default_teleport_player_to_lobby_on_join", true )) {
				$this->teleportPlayerToLobbyWorld ( $player );
			} else {
				$this->teleportPlayerToHomeWorld ( $player );
			}
		}
	}
	public function teleportPlayerToHomeWorld(Player $player) {
		try {
			$homeWorld = $this->getPlugin ()->setup->getHomeWorldName ();
			$homePos = $this->getPlugin ()->setup->getHomeWorldPos ();
			PortalManager::doTeleporting ( $player, $homeWorld, $homePos );
		} catch ( \Exception $e ) {
			$this->getPlugin ()->getLogger ()->info ( "handlePlayerRejoinThePlay error: " . $e->getMessage () );
		}
	}
	public function teleportPlayerToLobbyWorld(Player $player) {
		try {
			$serverWorld = $this->getPlugin ()->setup->getServerLobbyWorldName ();
			$serverPos = $this->getPlugin ()->setup->getServerLobbyPos ();
			PortalManager::doTeleporting ( $player, $serverWorld, $serverPos );
		} catch ( \Exception $e ) {
			$this->getPlugin ()->getLogger ()->info ( "handlePlayerRejoinThePlay error: " . $e->getMessage () );
		}
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param unknown $event        	
	 * @return boolean
	 */
	public function handlePlayerDeath(Player $player, &$event) {
		$this->getPlugin ()->controller->cleanUpHiderTrailer ( $player );
		$cancelEvent = false;
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			if ($arena instanceof ArenaModel) {
				if ($arena->status == ArenaModel::ARENA_STATUS_PLAYING) {
					if (isset ( $arena->seekers [$player->getName ()] )) {
						$message = TextFormat::WHITE . $this->getMsg ( "bh.play.warning.seekerkilled" ) . "[" . TextFormat::RED . $player->getName () . TextFormat::WHITE . "] - Respawn\n";
						// $message .= TextFormat::GRAY . $this->getMsg ( "bh.play.warning.remainseeker" ) . " [" . TextFormat::AQUA . count ( $arena->seekers ) . TextFormat::GRAY . "] | " . $this->getMsg ( "bh.play.warning.remainhider" ) . " [" . TextFormat::BLUE . count ( $arena->hidders ) . TextFormat::GRAY . "]\n";
						Server::getInstance ()->broadcastMessage ( $message, $arena->level->getPlayers () );
						$cancelEvent = true;
						$player->setHealth ( 10 );
						break;
					}
					if (isset ( $arena->hidders [$player->getName ()] )) {
						BlockHuntGameKit::removePlayerAllInventories ( $player );
						unset ( $arena->hidders [$player->getName ()] );
						unset ( $arena->hiderblocks [$player->getName ()] );
						$message = TextFormat::WHITE . $this->getMsg ( "bh.play.warning.hiderkilled" ) . "[" . TextFormat::RED . $player->getName () . "]\n";
						$message .= TextFormat::GRAY . $this->getMsg ( "bh.play.warning.remainseeker" ) . " [" . TextFormat::AQUA . count ( $arena->seekers ) . TextFormat::GRAY . "] | " . $this->getMsg ( "bh.play.warning.remainhider" ) . "[" . TextFormat::BLUE . count ( $arena->hidders ) . TextFormat::GRAY . "]\n";
						Server::getInstance ()->broadcastMessage ( $message, $arena->level->getPlayers () );
						if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
							$this->getPlugIn ()->gameKit->putOnGameKit ( $player, BlockHuntGameKit::KIT_SEEKER );
							$arena->seekers [$player->getName ()] = $player;
							$player->teleport ( $arena->seeker_warp );
							$player->setHealth ( 10 );
							$cancelEvent = true;
							try {
								// record a lost
								$this->plugin->profileprovider->upsetPlayerLoss ( $player->getName () );
								$this->plugin->profileprovider->addPlayerLossHider ( $player->getName () );
							} catch ( Exception $e ) {
								$this->plugin->getLogger ()->info ( $e->getMessage () . "|" . $e->getLine () . "|" . $e->getTraceAsString () ) . "\n";
							}
							break;
						}
					}
				}
			}
		}
		return $cancelEvent;
	}
	
	/**
	 *
	 * @param Player $player        	
	 */
	public function handlePlayerLeavethePlay(Player $player) {
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			if ($arena instanceof ArenaModel) {
				if (isset ( $arena->joinedplayers [$player->getName ()] )) {
					unset ( $arena->joinedplayers [$player->getName ()] );
					$player->teleport ( $arena->lobby );
				}
				if (isset ( $arena->seekersoriginal [$player->getName ()] )) {
					unset ( $arena->seekersoriginal [$player->getName ()] );
				}
				
				if (isset ( $arena->hidders [$player->getName ()] )) {
					unset ( $arena->hidders [$player->getName ()] );
					unset ( $arena->hiderblocks [$player->getName ()] );
					BlockHuntGameKit::removePlayerAllInventories ( $player );
					self::cleanUpHiderTrailer ( $player );
					$message = $this->getMsg ( TextFormat::GRAY . "bh.play.warning.hiderleft" ) . " " . TextFormat::YELLOW . $player->getName () . "\n";
					$message .= $this->getMsg ( "bh.play.warning.remainseeker" ) . " [" . count ( $arena->seekers ) . "] | " . $this->getMsg ( "bh.play.warning.remainhider" ) . "[" . count ( $arena->hidders ) . "]\n";
					Server::getInstance ()->broadcastMessage ( $message, $arena->level->getPlayers () );
					$player->teleport ( $arena->lobby );
					self::removeBlock ( $player, $player->getPosition () );
					self::removeBlock ( $player, new Position ( $player->lastX, $player->lastY, $player->lastZ, $player->getLevel () ) );
					try {
						// record a lost
						$this->plugin->profileprovider->upsetPlayerLoss ( $player->getName () );
						$this->plugin->profileprovider->addPlayerLossHider ( $player->getName () );
					} catch ( Exception $e ) {
						$this->plugin->getLogger ()->info ( $e->getMessage () . "|" . $e->getLine () . "|" . $e->getTraceAsString () ) . "\n";
					}
				}
				
				if (isset ( $arena->seekers [$player->getName ()] )) {
					unset ( $arena->seekers [$player->getName ()] );
					BlockHuntGameKit::removePlayerAllInventories ( $player );
					$message = $this->getMsg ( "bh.play.warning.seekerleft" ) . " " . $player->getName () . "\n";
					$message .= $this->getMsg ( "bh.play.warning.remainseeker" ) . " [" . count ( $arena->seekers ) . "] | " . $this->getMsg ( "bh.play.warning.remainhider" ) . "[" . count ( $arena->hidders ) . "]\n";
					Server::getInstance ()->broadcastMessage ( $message, $arena->level->getPlayers () );
					$player->teleport ( $arena->lobby );
					try {
						// record a lost
						$this->plugin->profileprovider->upsetPlayerLoss ( $player->getName () );
						$this->plugin->profileprovider->addPlayerLossSeeker ( $player->getName () );
					} catch ( Exception $e ) {
						$this->plugin->getLogger ()->info ( $e->getMessage () . "|" . $e->getLine () . "|" . $e->getTraceAsString () ) . "\n";
					}
				}
			}
		}
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @return boolean
	 */
	public function handlePlayerGotHurt(Player $player) {
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			foreach ( $arena->hidders as $hider ) {
				if (isset ( $arena->hidders [$player->getName ()] )) {
					$this->cleanUpHiderTrailer ( $player );
					$this->cleanUpHiderTrailer ( $player );
					if ($player->getHealth () < 9) {
						$this->getPlugin ()->controller->cleanUpHiderTrailer ( $player );
						unset ( $arena->hidders [$player->getName ()] );
						unset ( $arena->hiderblocks [$player->getName ()] );
						$message = $this->getMsg ( "bh.play.warning.hider" ) . " [" . $hider->getName () . "] " . $this->getMsg ( "bh.play.warning.killedbyseeker" ) . " [" . $player->getName () . "]\n";
						Server::getInstance ()->broadcastMessage ( $message, $arena->hidders );
						$arena->seekers [$player->getName ()] = $player;
						$message .= $this->getMsg ( "bh.play.warning.remainseeker" ) . " [" . count ( $arena->seekers ) . "] | " . $this->getMsg ( "bh.play.warning.remainhider" ) . "[" . count ( $arena->hidders ) . "]\n";
						Server::getInstance ()->broadcastMessage ( $message, $arena->hidders );
						Server::getInstance ()->broadcastMessage ( $message, $arena->seekers );
						BlockHuntGameKit::removePlayerAllInventories ( $hider );
						$player->teleport ( $arena->seeker_warp );
						$this->getPlugIn ()->gameKit->putOnGameKit ( $player, BlockHuntGameKit::KIT_SEEKER );
						$player->setHealth ( 6 );
						return true;
						break;
					}
				} elseif (isset ( $arena->seekers [$player->getName ()] )) {
					if ($player->getHealth () < 9) {
						$player->setHealth ( 15 );
					}
				}
			}
		}
		return false;
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param Player $attacker        	
	 * @return boolean
	 */
	public function cancelPlayerGotHurtEvent(Player $player, Player $attacker) {
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			if ($arena->count_down != 0) {
				if (isset ( $arena->joinedplayers [$player->getName ()] ) && isset ( $arena->joinedplayers [$player->getName ()] )) {
					return true;
					break;
				}
			}
			if (($arena->status === ArenaModel::ARENA_STATUS_PLAYING)) {
				if (isset ( $arena->hidders [$player->getName ()] ) && isset ( $arena->hidders [$attacker->getName ()] )) {
					return true;
					break;
				}
				if (isset ( $arena->seekers [$player->getName ()] ) && isset ( $arena->seekers [$attacker->getName ()] )) {
					return true;
					break;
				}
			}
		}
		if (isset ( $this->getPlugin ()->defenceManager->defences [DefenceModel::DEFENCE_LOBBY] )) {
			$defence = $this->getPlugin ()->defenceManager->defences [DefenceModel::DEFENCE_LOBBY];
			if ($defence != null && $defence->contains ( $player->getPosition () )) {
				return true;
			}
			return false;
		} else {
			echo "\n Defence not found! \n";
		}
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param unknown $block        	
	 */
	public function handleTapOnHiderBlock(Player $player, $block) {
		try {
			foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
				foreach ( $arena->hidders as $hider ) {
					if ($hider instanceof Player) {
						$dist = round ( $player->distance ( $hider->getPosition () ) );
						if ($dist === 0 || $dist === 1 || $dist <= 3) {
							$player->showPlayer ( $hider );
							$ev = new EntityDamageByEntityEvent ( $player, $hider, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 0.4 );
							$hider->attack ( 2, $ev );
							if ($hider->getHealth () < 8) {
								BlockHuntController::removeBlock ( $hider, $hider->getPosition () );
								unset ( $arena->hidders [$hider->getName ()] );
								unset ( $arena->hiderblocks [$hider->getName ()] );
								$arena->seekers [$hider->getName ()] = $hider;
								$hider->showPlayer ( $player );
								foreach ( $arena->seekers as $seeker ) {
									$seeker->showPlayer ( $hider );
									$hider->showPlayer ( $seeker );
								}
								$message = TextFormat::GRAY . "[BH] hider [" . TextFormat::RED . $hider->getName () . TextFormat::GRAY . "] killed by seeker [" . TextFormat::GOLD . $player->getName () . TextFormat::GRAY . "]\n";
								// $message .= "[BH] remain Seekers [" . count ( $arena->seekers ) . "] | Hiders [" . count ( $arena->hidders ) . "]\n";
								Server::getInstance ()->broadcastMessage ( $message, $arena->level->getPlayers () );
								BlockHuntGameKit::removePlayerAllInventories ( $hider );
								$hider->teleport ( $arena->seeker_warp );
								$this->getPlugIn ()->gameKit->putOnGameKit ( $player, BlockHuntGameKit::KIT_SEEKER );
								break;
							}
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			$this->log ( "tap error: " . $e->getMessage () );
		}
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param Position $from        	
	 * @param Position $to        	
	 */
	public function moveHiderBlock(Player $player, Position $from, Position $to) {
		$blockid = $this->getHiderBlock ( $player );
		if (! is_null ( $blockid ) && ! empty ( $blockid )) {
			$this->keepBlockMoving ( $player, $from, $to, $blockid );
		}
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @return NULL
	 */
	public function getHiderBlock(Player $player) {
		$blockid = null;
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			if (isset ( $arena->hiderblocks [$player->getName ()] )) {
				$blockid = $arena->hiderblocks [$player->getName ()];
				break;
			}
		}
		return $blockid;
	}
	
	/**
	 *
	 * @param Player $player        	
	 */
	public function handlePlayerBreakBlock(Player $player) {
		$blockid = $this->getHiderBlock ( $player );
		if ($blockid != null && ! empty ( $blockid )) {
			$this->keepBlockMoving ( $player, new Position ( $player->lastX, $player->lastY, $player->lastZ ), $player->getPosition (), $blockid );
		}
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param Position $from        	
	 * @param Position $to        	
	 * @param unknown $blockid        	
	 */
	public function keepBlockMoving(Player $player, Position $from, Position $to, $blockid) {
		foreach ( $player->getViewers () as $vp ) {
			if ($vp->getName () != $player->getName ()) {
				$player->hidePlayer ( $vp );
			}
		}
		$envbid = $player->getLevel ()->getBlockIdAt ( $from->x, $from->y, $from->z );
		if ($envbid === $blockid) {
			$player->getLevel ()->setBlock ( new Position ( $from->x, $from->y, $from->z, $player->getLevel () ), $block = Item::get ( Item::AIR )->getBlock (), true, false );
		}
		$envbid = $player->getLevel ()->getBlockIdAt ( $player->x, $player->y, $player->z );
		if ($envbid === Item::AIR || $envbid === Item::TALL_GRASS) {
			$player->getLevel ()->setBlock ( $player->getPosition (), Item::get ( $blockid )->getBlock (), true, false );
		} elseif ($envbid === $blockid) {
			$player->getLevel ()->setBlock ( $player->getPosition (), Item::get ( $blockid )->getBlock (), true, false );
		}
		if (round ( $player->x ) != round ( $from->x ) || round ( $player->y ) != round ( $from->y ) || round ( $player->z ) != round ( $from->z )) {
			$envbid = $player->level->getBlockIdAt ( $to->x, $to->y, $to->z );
			if ($envbid === Item::AIR || $envbid === Item::TALL_GRASS) {
				$player->level->setBlock ( $to, Item::get ( $blockid )->getBlock (), true, false );
			}
		}
	}
	
	/**
	 *
	 * @param Player $player        	
	 */
	public function cleanUpHiderTrailer(Player $player) {
		try {
			$player->onGround = true;
			self::removeBlock ( $player, new Position ( round ( $player->lastX ), round ( $player->lastY ), round ( $player->lastZ ) ) );
			self::removeBlock ( $player, new Position ( round ( $player->x ), round ( $player->y ), round ( $player->z ) ) );
			self::removeBlock ( $player, new Position ( $player->lastX, $player->lastY, $player->lastZ ) );
			self::removeBlock ( $player, $player->getPosition () );
		} catch ( \Exception $e ) {
		}
		$player->onGround = false;
	}
	
	/**
	 *
	 * @param Player $player        	
	 */
	public function cleanKnowback(Player $player) {
		try {
			$player->onGround = true;
			self::removeBlock ( $player, new Position ( round ( $player->lastX ), round ( $player->lastY ), round ( $player->lastZ ) ) );
			self::removeBlock ( $player, new Position ( round ( $player->x ), round ( $player->y ), round ( $player->z ) ) );
			self::removeBlock ( $player, new Position ( $player->lastX, $player->lastY, $player->lastZ ) );
			self::removeBlock ( $player, $player->getPosition () );
		} catch ( \Exception $e ) {
		}
		$player->onGround = false;
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param Position $pos        	
	 */
	final static public function removeBlock(Player $player, Position $pos) {
		$player->level->setBlock ( new Position ( $pos->x, $pos->y, $pos->z, $player->getLevel () ), Item::get ( Item::AIR )->getBlock (), true, false );
	}
	
	/**
	 *
	 * @param unknown $message        	
	 * @param string $viewers        	
	 */
	public static function broadcastPopUpText($message, $viewers = null) {
		$packet = new TextPacket ();
		$packet->type = TextPacket::TYPE_POPUP;
		$packet->message = $message;
		$packet->setChannel ( Network::CHANNEL_TEXT );
		if ($viewers != null) {
			Server::getInstance ()->broadcastPacket ( $viewers, $packet );
		}
	}
	
	/**
	 *
	 * @param unknown $message        	
	 * @param string $viewers        	
	 */
	public static function broadcastPopUpTips($message, $viewers = null) {
		$packet = new TextPacket ();
		$packet->type = TextPacket::TYPE_TIP;
		$packet->message = $message;
		$packet->setChannel ( Network::CHANNEL_TEXT );
		if ($viewers != null) {
			Server::getInstance ()->broadcastPacket ( $viewers, $packet );
		}
	}
	
	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {
	}
	
	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {
	}
}