<?php

namespace mcg76\game\blockhunt\arenas;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\level\sound\DoorSound;
use mcg76\game\blockhunt\BlockHuntController;

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
 * MCG76 Arena Model
 */
class ArenaModel {
	const ARENA_DIR = 'arenas/';
	const ARENA_TYPE_BLOCKHUNT = 'blockhunt_arena';
	const ARENA_STATUS_AVAILABLE = "available";
	const ARENA_STATUS_WAITING = "waiting";
	const ARENA_STATUS_PLAYING = "playing";
	const ARENA_STATUS_COUNT_DOWN = "count down";
	const ARENA_GATE_OPEN = "gate_open";
	const ARENA_GATE_CLOSE = "gate_close";
	public $id;
	public $name;
	public $type;
	public $playLevel;
	public $level;
	public $levelName;
	public $entrance;
	public $exit;
	public $seeker_warp;
	public $hider_warp;
	public $lobby;
	public $position;
	public $pos1;
	public $pos2;
	public $reset_time = 280;
	public $capacity = 6;
	public $reward = 12;
	public $size = 16;
	public $signJoin;
	public $signJoinSeeker;
	public $signJoinSeeker2;
	public $signJoinHider;
	public $signJoinHider2;
	public $signStats;
	public $signStats2;
	public $signExit;
	public $signExit2;
	public $players = 0;
	public $joinedplayers = [ ];
	public $status = self::ARENA_STATUS_AVAILABLE;
	public $count_down = 20;
	public $seekers = [ ];
	public $seekersoriginal = [];
	public $hidders = [ ];
	public $hiderblocks = [ ];
	public $blocks = [ ];
	public $min = 2;
	public $max = 30;
	public $minSeeker = 1;
	public $minHider = 1;

	public $allowSeekers = 10;
	public $allowHiders = 60;
	public $time_release_seeker = 30;
	public $seekergate1;
	public $seekergate2;
	public $timeOutTask = null;
	public $playStartTime = null;
	public $playFinishTime = null;
	public $seekerReleaseTime = 20;
	public $reminderCountDown = 25;
	public $portalPos1 = null;
	public $portalPos2 = null;	
	public $active = false;
	
	//cache
	public $cacheBlocks=[];
	//lock down
	public $lockdownOn = false;
	public $backupArenaHeight = 5;
	public $resetBackUpArenaHeight = 5;
	public $resetNewGame = false;
	public $resetCountDown = 5;
	
	public function __construct($name, $levelName, $position) {
		$this->id = time ();
		$this->name = $name;
		$this->levelName = $levelName;
		$this->position = $position;
	}
	/**
	 *
	 * @param string $path
	 */
	public function activateArena($path) {
		$name = $this->name;
		$data = new Config ( $path . "$name.yml", Config::YAML );
		$data->set ( "active", true );
		$data->save ();
	}
	
	/**
	 *
	 * @param  $path
	 */
	public function deactiveArena($path) {
		$name = $this->name;
		$data = new Config ( $path . "$name.yml", Config::YAML );
		$data->set ( "active", false );
		$data->save ();
	}
		
	/**
	 * 
	 * @param unknown $pos
	 * @return boolean
	 */
	public function insidePortal($pos) {
		$p1 = new Position ( $this->portalPos1->x, $this->portalPos1->y, $this->portalPos1->z );
		$p2 = new Position ( $this->portalPos2->x, $this->portalPos2->y + 1, $this->portalPos2->z );
		if ((min ( $p1->getX (), $p2->getX () ) <= $pos->getX ()) && (max ( $p1->getX (), $p2->getX () ) >= $pos->getX ()) && (min ( $p1->getY (), $p2->getY () ) <= $pos->getY ()) && (max ( $p1->getY (), $p2->getY () ) >= $pos->getY ()) && (min ( $p1->getZ (), $p2->getZ () ) <= $pos->getZ ()) && (max ( $p1->getZ (), $p2->getZ () ) >= $pos->getZ ())) {
			return true;
		} else {
			return false;
		}
	}
	
	public function insideArena($pos) {
		$p1 = new Position ( $this->pos1->x, $this->pos1->y, $this->pos1->z);
		$p2 = new Position ( $this->pos2->x, $this->pos2->y + 1, $this->pos2->z );
		if ((min ( $p1->getX (), $p2->getX () ) <= $pos->getX ()) && (max ( $p1->getX (), $p2->getX () ) >= $pos->getX ()) && (min ( $p1->getY (), $p2->getY () ) <= $pos->getY ()) && (max ( $p1->getY (), $p2->getY () ) >= $pos->getY ()) && (min ( $p1->getZ (), $p2->getZ () ) <= $pos->getZ ()) && (max ( $p1->getZ (), $p2->getZ () ) >= $pos->getZ ())) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 
	 * @param Player $player
	 * @return void|boolean
	 */
	public function portalEnter(Player $player) {
		if ($player->getLevel ()->getName () != $this->levelName) {
			return true;
		}
		if ($this->insidePortal ( $player->getPosition () )) {
			if (! isset ( $this->joinedPlayers [$player->getName ()] )) {
				$this->joinedplayers [$player->getName ()] = $player;
				$player->sendMessage ( TextFormat::GRAY . "[BH] " . $player->getName () . " joined " . $this->displayName . " [" . TextFormat::GREEN . count ( $this->joinedPlayers ) . "/min." . $this->min . "]" );
				$this->level->addSound ( new DoorSound ( $player ), $this->joinedPlayers );
				BlockHuntController::addParticles ( $player->getLevel (), $player->getPosition (), 100 );
				if (! $player->isOp () && ! $player->isSurvival ()) {
					$player->setGamemode ( Player::SURVIVAL );
				}
				$player->sendPopup ( "[BH] Pick a role as Seeker or Hider or wait here for a random pick!" );
				$player->sendTip ( "[BH] Game automatically start with minimal players." );
			}
		} else {
			if (isset ( $this->joinedPlayers [$player->getName ()] )) {
				$player->sendTip ( "[BH] " . $player->getName () . " left " . $this->name );
				$this->level->addSound ( new DoorSound ( $player ), $$this->joinedplayers );
				unset ( $this->joinedPlayers [$player->getName ()] );
			}
		}
        return;
	}
	
	/**
	 *
	 * @param string $path        	
	 */
	public function save($path) {
		$xpath = $path . self::ARENA_DIR;
		if (! file_exists ( $xpath )) {
			@mkdir ( $xpath, 0755, true );
		}
		$name = $this->name;
		$data = new Config ( $path . self::ARENA_DIR . "$name.yml", Config::YAML );
		$data->set ( "id", time () );
		$data->set ( "name", $name );
		$data->set ( "type", $this->type );
		$data->set ( "levelName", $this->levelName );
		if ($this->entrance != null && $this->entrance != false) {
			$data->set ( "entranceX", round ( $this->entrance->x ) );
			$data->set ( "entranceY", round ( $this->entrance->y ) );
			$data->set ( "entranceZ", round ( $this->entrance->z ) );
		}
		
		if ($this->seekergate1 != null && $this->seekergate1 != false) {
			$data->set ( "seekergate1X", round ( $this->seekergate1->x ) );
			$data->set ( "seekergate1Y", round ( $this->seekergate1->y ) );
			$data->set ( "seekergate1Z", round ( $this->seekergate1->z ) );
		}
		if ($this->seekergate2 != null && $this->seekergate2 != false) {
			$data->set ( "seekergate2X", round ( $this->seekergate2->x ) );
			$data->set ( "seekergate2Y", round ( $this->seekergate2->y ) );
			$data->set ( "seekergate2Z", round ( $this->seekergate2->z ) );
		}
		
		if ($this->exit != null && $this->exit != false) {
			$data->set ( "exitX", round ( $this->exit->x ) );
			$data->set ( "exitY", round ( $this->exit->y ) );
			$data->set ( "exitZ", round ( $this->exit->z ) );
		}
		if ($this->lobby != null && $this->lobby != false) {
			$data->set ( "lobbyX", round ( $this->lobby->x ) );
			$data->set ( "lobbyY", round ( $this->lobby->y ) );
			$data->set ( "lobbyZ", round ( $this->lobby->z ) );
		}
		
		if ($this->position != null && $this->position != false) {
			$data->set ( "positionX", round ( $this->position->x ) );
			$data->set ( "positionY", round ( $this->position->y ) );
			$data->set ( "positionZ", round ( $this->position->z ) );
		}
		if ($this->pos1 != null && $this->pos1 != false) {
			$data->set ( "pos1X", round ( $this->pos1->x ) );
			$data->set ( "pos1Y", round ( $this->pos1->y ) );
			$data->set ( "pos1Z", round ( $this->pos1->z ) );
		}
		
		if ($this->pos2 != null && $this->pos2 != false) {
			$data->set ( "pos2X", round ( $this->pos2->x ) );
			$data->set ( "pos2Y", round ( $this->pos2->y ) );
			$data->set ( "pos2Z", round ( $this->pos2->z ) );
		}
		
		if ($this->seeker_warp != null && $this->seeker_warp != false) {
			$data->set ( "seekerX", round ( $this->seeker_warp->x ) );
			$data->set ( "seekerY", round ( $this->seeker_warp->y ) );
			$data->set ( "seekerZ", round ( $this->seeker_warp->z ) );
		}
		
		if ($this->signJoinSeeker != null && $this->signJoinSeeker != false) {
			$data->set ( "signJoinSeekerX", round ( $this->signJoinSeeker->x ) );
			$data->set ( "signJoinSeekerY", round ( $this->signJoinSeeker->y ) );
			$data->set ( "signJoinSeekerZ", round ( $this->signJoinSeeker->z ) );
		}
		
		if ($this->signJoinSeeker2 != null && $this->signJoinSeeker2 != false) {
			$data->set ( "signJoinSeeker2X", round ( $this->signJoinSeeker2->x ) );
			$data->set ( "signJoinSeeker2Y", round ( $this->signJoinSeeker2->y ) );
			$data->set ( "signJoinSeeker2Z", round ( $this->signJoinSeeker2->z ) );
		}
		
		if ($this->signJoinHider != null && $this->signJoinHider != false) {
			$data->set ( "signJoinHiderX", round ( $this->signJoinHider->x ) );
			$data->set ( "signJoinHiderY", round ( $this->signJoinHider->y ) );
			$data->set ( "signJoinHiderZ", round ( $this->signJoinHider->z ) );
		}
		
		if ($this->signJoinHider2 != null && $this->signJoinHider2 != false) {
			$data->set ( "signJoinHider2X", round ( $this->signJoinHider2->x ) );
			$data->set ( "signJoinHider2Y", round ( $this->signJoinHider2->y ) );
			$data->set ( "signJoinHider2Z", round ( $this->signJoinHider2->z ) );
		}
		
		if ($this->hider_warp != null && $this->hider_warp != false) {
			$data->set ( "hiderX", round ( $this->hider_warp->x ) );
			$data->set ( "hiderY", round ( $this->hider_warp->y ) );
			$data->set ( "hiderZ", round ( $this->hider_warp->z ) );
		}
		if ($this->signJoin != null && $this->signJoin != false) {
			$data->set ( "signJoinX", round ( $this->signJoin->x ) );
			$data->set ( "signJoinY", round ( $this->signJoin->y ) );
			$data->set ( "signJoinZ", round ( $this->signJoin->z ) );
		}
		
		if ($this->signStats != null && $this->signStats != false) {
			$data->set ( "signStatsX", round ( $this->signStats->x ) );
			$data->set ( "signStatsY", round ( $this->signStats->y ) );
			$data->set ( "signStatsZ", round ( $this->signStats->z ) );
		}

		if ($this->signStats2 != null && $this->signStats2 != false) {
			$data->set ( "signStats2X", round ( $this->signStats2->x ) );
			$data->set ( "signStats2Y", round ( $this->signStats2->y ) );
			$data->set ( "signStats2Z", round ( $this->signStats2->z ) );
		}
		
		if ($this->signExit != null && $this->signExit != false) {
			$data->set ( "signExitX", round ( $this->signExit->x ) );
			$data->set ( "signExitY", round ( $this->signExit->y ) );
			$data->set ( "signExitZ", round ( $this->signExit->z ) );
		}
		if ($this->signExit2 != null && $this->signExit2 != false) {
			$data->set ( "signExit2X", round ( $this->signExit2->x ) );
			$data->set ( "signExit2Y", round ( $this->signExit2->y ) );
			$data->set ( "signExit2Z", round ( $this->signExit2->z ) );
		}
		
		if ($this->portalPos1 != null && $this->portalPos1 != false) {
			$data->set ( "portal1X", round ( $this->portalPos1->x ) );
			$data->set ( "portal1Y", round ( $this->portalPos1->y ) );
			$data->set ( "portal1Z", round ( $this->portalPos1->z ) );
		}
		
		if ($this->portalPos2 != null && $this->portalPos2 != false) {
			$data->set ( "portal2X", round ( $this->portalPos2->x ) );
			$data->set ( "portal2Y", round ( $this->portalPos2->y ) );
			$data->set ( "portal2Z", round ( $this->portalPos2->z ) );
		}
		
		$data->set ( "capacity", $this->capacity );
		$data->set ( "reward", $this->reward );
		$data->set ( "reset_time", $this->reset_time );
		$data->set ( "size", $this->size );
		$data->set ( "count_down", $this->count_down );
		$data->set ( "blocks", $this->blocks );
		
		$data->set ( "allowSeekers", $this->allowSeekers );
		$data->set ( "allowHiders", $this->allowHiders );		
		$data->set ( "seekerReleaseTime", $this->seekerReleaseTime );		
		$data->set ( "min", $this->min );
		$data->set ( "max", $this->max );		
		$data->set ( "minHider", $this->minHider );
		$data->set ( "minSeeker", $this->minSeeker );
		$data->set ( "reminderCountDown", $this->reminderCountDown );		
		$data->set ( "active", $this->active );		
		$data->set ( "reset_backup_arena_height", $this->resetBackUpArenaHeight );
		$data->set ( "reset_new_game", $this->resetNewGame );
		$data->set ( "reset_count_down", $this->resetCountDown );
		
		$data->save ();
	}
	
	/**
	 *
	 * @param string $path        	
	 * @param string $name        	
	 * @return ArenaModel
	 */
	public static function load($path, $name) {
		$arena = null;
		if (! file_exists ( $path . self::ARENA_DIR . "$name.yml" )) {
			return null;
		}
		$data = new Config ( $path . self::ARENA_DIR . "$name.yml", Config::YAML );
		$data->getAll ();
		if ($data != null) {
			$arena = new ArenaModel ( $data->get ( "name" ), $data->get ( "levelName" ), null );
			
			$arena->id = $data->get ( "id" );
			$arena->name = $data->get ( "name" );
			$arena->type = $data->get ( "type" );
			$arena->levelName = $data->get ( "levelName" );
			$px = $data->get ( "positionX" );
			$py = $data->get ( "positionY" );
			$pz = $data->get ( "positionZ" );
			$arena->position = new Position ( $px, $py, $pz );
			
			$kx = $data->get ( "entranceX" );
			$ky = $data->get ( "entranceY" );
			$kz = $data->get ( "entranceZ" );
			$arena->entrance = new Position ( $kx, $ky, $kz );
			
			$kx = $data->get ( "exitX" );
			$ky = $data->get ( "exitY" );
			$kz = $data->get ( "exitZ" );
			$arena->exit = new Position ( $kx, $ky, $kz );
			
			$kx = $data->get ( "lobbyX" );
			$ky = $data->get ( "lobbyY" );
			$kz = $data->get ( "lobbyZ" );
			$arena->lobby = new Position ( $kx, $ky, $kz );
			
			$kx = $data->get ( "pos1X" );
			$ky = $data->get ( "pos1Y" );
			$kz = $data->get ( "pos1Z" );
			$arena->pos1 = new Position ( $kx, $ky, $kz );
			
			$bx = $data->get ( "pos2X" );
			$by = $data->get ( "pos2Y" );
			$bz = $data->get ( "pos2Z" );
			$arena->pos2 = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "signJoinX" );
			$by = $data->get ( "signJoinY" );
			$bz = $data->get ( "signJoinZ" );
			$arena->signJoin = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "signStatsX" );
			$by = $data->get ( "signStatsY" );
			$bz = $data->get ( "signStatsZ" );
			$arena->signStats = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "signStats2X" );
			$by = $data->get ( "signStats2Y" );
			$bz = $data->get ( "signStats2Z" );
			$arena->signStats2 = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "signExitX" );
			$by = $data->get ( "signExitY" );
			$bz = $data->get ( "signExitZ" );
			$arena->signExit = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "seekerX" );
			$by = $data->get ( "seekerY" );
			$bz = $data->get ( "seekerZ" );
			$arena->seeker_warp = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "hiderX" );
			$by = $data->get ( "hiderY" );
			$bz = $data->get ( "hiderZ" );
			$arena->hider_warp = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "signJoinSeekerX" );
			$by = $data->get ( "signJoinSeekerY" );
			$bz = $data->get ( "signJoinSeekerZ" );
			$arena->signJoinSeeker = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "signJoinSeeker2X" );
			$by = $data->get ( "signJoinSeeker2Y" );
			$bz = $data->get ( "signJoinSeeker2Z" );
			$arena->signJoinSeeker2 = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "signJoinHiderX" );
			$by = $data->get ( "signJoinHiderY" );
			$bz = $data->get ( "signJoinHiderZ" );
			$arena->signJoinHider = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "signJoinHider2X" );
			$by = $data->get ( "signJoinHider2Y" );
			$bz = $data->get ( "signJoinHider2Z" );
			$arena->signJoinHider2 = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "seekergate1X" );
			$by = $data->get ( "seekergate1Y" );
			$bz = $data->get ( "seekergate1Z" );
			$arena->seekergate1 = new Position ( $bx, $by, $bz );
			
			$bx = $data->get ( "seekergate2X" );
			$by = $data->get ( "seekergate2Y" );
			$bz = $data->get ( "seekergate2Z" );
			$arena->seekergate2 = new Position ( $bx, $by, $bz );
			
			$arena->capacity = $data->get ( "capacity", 20 );
			$arena->reward = $data->get ( "reward", 12 );
			$arena->reset_time = $data->get ( "reset_time", 300 );
			$arena->size = $data->get ( "size", 20 );
			$arena->count_down = $data->get ( "count_down", 10 );
			$arena->blocks = $data->get ( "blocks" );
			
			$arena->allowSeekers = $data->get ( "allowSeekers", 10 );
			$arena->allowHiders = $data->get ( "allowHiders", 10 );
			
			$arena->seekerReleaseTime = $data->get ( "seekerReleaseTime", 25 );
			$arena->min = $data->get ( "min", 2 );
			$arena->max = $data->get ( "max", 20 );
			
			$arena->minSeeker = $data->get ( "minSeeker", 1 );
			$arena->minHider = $data->get ( "minHider", 1);
			
			$arena->backupArenaHeight = $data->get ( "backupArenaHeight", 5);						
			return $arena;
		}
		return null;
	}
	public function contains($pos) {
		if ((min ( $this->pos1->getX (), $this->pos2->getX () ) <= $pos->getX ()) && (max ( $this->pos1->getX (), $this->pos2->getX () ) >= $pos->getX ()) && (min ( $this->pos1->getY (), $this->pos2->getY () ) <= $pos->getY ()) && (max ( $this->pos1->getY (), $this->pos2->getY () ) >= $pos->getY ()) && (min ( $this->pos1->getZ (), $this->pos2->getZ () ) <= $pos->getZ ()) && (max ( $this->pos1->getZ (), $this->pos2->getZ () ) >= $pos->getZ ())) {
			return true;
		} else {
			return false;
		}
	}
	public function getData($path) {
        $name = $this->name;
		if (! file_exists ( $path . self::ARENA_DIR . "$name.yml" )) {
			return null;
		}
		$data = new Config ( $path . self::ARENA_DIR . "$name.yml", Config::YAML );
		$data->getAll ();
		return $data;
	}
	public static function getArenaData($path, $name) {
		if (! file_exists ( $path . self::ARENA_DIR . "$name.yml" )) {
			return null;
		}
		$data = new Config ( $path . self::ARENA_DIR . "$name.yml", Config::YAML );
		$data->getAll ();
		return $data;
	}
	public function toString() {
		$output = "";
		$output .= "id : " . $this->id . "\n";
		$output .= "name : " . $this->name . "\n";
		$output .= "type : " . $this->type . "\n";
		$output .= "levelName : " . $this->levelName . "\n";
		$output .= "capacity : " . $this->capacity . "\n";
		if ($this->position != null && $this->position != false) {
			$output .= "position : " . $this->position->x . " " . $this->position->y . " " . $this->position->z . "\n";
		}
		if ($this->pos1 != null && $this->pos1 != false) {
			$output .= "pos1 : " . $this->pos1->x . " " . $this->pos1->y . " " . $this->pos1->z . "\n";
		}
		if ($this->pos2 != null && $this->pos2 != false) {
			$output .= "pos2 : " . $this->pos2->x . " " . $this->pos2->y . " " . $this->pos2->z . "\n";
		}
		$output .= "reward : " . $this->reward . "\n";
		$output .= "reset_time : " . $this->reset_time . "\n";
		return $output;
	}
	public function delete($path) {
		$name = $this->name;
		@unlink ( $path . ArenaModel::ARENA_DIR . "$name.yml" );
	}
}