<?php

namespace mcg76\game\blockhunt\utils;

use pocketmine\Server;
use pocketmine\Player;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use pocketmine\utils\TextFormat;
use mcg76\game\blockhunt\arenas\ArenaBlock;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;

/**
 * MCG76 LevelUtil
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class LevelUtil {
	const WORLD_FOLDER = "worlds/";
	public static function createSessionWorld($sourceWorldName, $targetWorldName) {
		$status = false;
		$copylevel = null;
		// make a copy of world
		$fileutil = new FileUtil ();
		$source = Server::getInstance ()->getDataPath () . self::WORLD_FOLDER . $sourceWorldName . "/";
		$destination = Server::getInstance ()->getDataPath () . self::WORLD_FOLDER . $targetWorldName . "/";
		// make new copy
		if ($fileutil->xcopy ( $source, $destination )) {
			try {
				Server::getInstance ()->loadLevel ( $destination );
				$copylevel = Server::getInstance ()->getLevelByName ( $destination );
				echo "[BH] Session world created [" . $destination . "]\n";
			} catch ( \Exception $e ) {
				echo "[BH]createSessionWorld error: " . $e->getMessage ();
			}
		} else {
			echo "problem creating BH world. please contact administrator.";
		}
		return $copylevel;
	}
	public static function deleteSessionWorld($worldname) {
		$status = false;
		try {
			Server::getInstance ()->loadLevel ( $worldname );
			// check if level exist
			$level = Server::getInstance ()->getLevelByName ( $worldname );
			if (is_null ( $level )) {
				echo "[BH]deleteSessionWorld not found: " . $worldname;
				return;
			}
			// unload level
			Server::getInstance ()->unloadLevel ( $level, true );
			$levelpath = Server::getInstance ()->getDataPath () . self::WORLD_FOLDER . $worldname . "/";
			$fileutil = new FileUtil ();
			$fileutil->unlinkRecursive ( $levelpath, true );
			echo "[BH] Session world deleted [" . $worldname . "]\n";
		} catch ( \Exception $e ) {
			echo "[BH]deleteSessionWorld error: " . $e->getMessage ();
		}
		return $status;
	}
	public function loadWorld(CommandSender $sender, $levelname) {
		if ($levelname == null) {
			$sender->sendMessage ( "Warning, no world name specified!" );
			return;
		}
		$sender->sendMessage ( "Load World: " . $levelname );
		if (! $sender->getServer ()->isLevelLoaded ( $levelname )) {
			$ret = $sender->getServer ()->loadLevel ( $levelname );
			if ($ret) {
				$sender->sendMessage ( "world loaded! " );
			} else {
				$sender->sendMessage ( "Error, unable load World: " . $levelname . " contact server administrator." );
			}
		}
		$this->listWorld ( $sender );
	}
	public function listAllWorld(CommandSender $sender) {
		$out = "The following levels are available:";
		$i = 0;
		if ($handle = opendir ( $levelpath = $sender->getServer ()->getDataPath () . "worlds/" )) {
			while ( false !== ($entry = readdir ( $handle )) ) {
				if ($entry [0] != ".") {
					$i ++;
					$out .= "\n " . $i . ">" . $entry . " ";
				}
			}
			closedir ( $handle );
		}
		$sender->sendMessage ( $out );
	}
// 	public static function lockdownArena(BlockHuntPlugIn $plugin, Player $player, $arenaName) {
// 		if (! $player->isOp ()) {
// 			$player->sendMessage ( "[BH] You are not authorinized to use this command." );
// 			return;
// 		}
// 		$startTime = microtime ( true );
// 		if (! isset ( $plugin->arenaManager->playArenas [$arenaName] )) {
// 			$player->sendMessage ( "[BH] arena not found!" );
// 			return;
// 		}
// 		$arena = $plugin->arenaManager->playArenas [$arenaName];
// 		$arena->lockdownOn = true;
// 		// // find all blocks
// 		$output = "";
// 		$player->sendMessage ( TextFormat::YELLOW . "[BH] processing lockdown....please wait!" );
// 		$blocks = self::getPlotBlocks ( $arena->pos1, $arena->pos2, false, $output );
// 		// $player->sendMessage ( TextFormat::GRAY . "[BH] " . $output );
// 		//$blocks2 = self::getPlotBlocks ( $arena->portalPos1, $arena->portalPos2, false, $output );
// 		// $player->sendMessage ( TextFormat::GRAY . "[BH] " . $output );
// 		if (empty ( $blocks1 ) || count ( $blocks1 ) === 0) {
// 			$player->sendMessage ( TextFormat::YELLOW . "[BH] Arena is empty! nothing to lockdown." );
// 			return;
// 		}
// 		//$blocks = array_merge ( $blocks1, $blocks2 );
// 		$plugin->cacheBlocks [$arenaName] = $blocks;
// 		$arena->save ( $plugin->getDataFolder () );
// 		$plugin->arenaManager->playArenas [$arenaName] = $arena;
// 		$player->sendMessage ( TextFormat::GREEN . "[BH] Arena Lock-down " . count ( $blocks ) . " completed in " . round ( (microtime ( true ) - $startTime) ) . "  seconds" );
// 		unset ( $blocks );
// 		return true;
// 	}
	
// 	public static function unLockArena(BlockHuntPlugIn $plugin, Player $player, $arenaName) {
// 		if (! $player->isOp ()) {
// 			$player->sendMessage ( "[BH] You are not authorinized to use this command." );
// 			return;
// 		}
// 		$startTime = microtime ( true );
// 		if (! isset ( $plugin->arenaManager->playArenas [$arenaName] )) {
// 			$player->sendMessage ( "[BH] arena not found!" );
// 			return;
// 		}
// 		$arena = $plugin->arenaManager->playArenas [$arenaName];
// 		if ($arena->lockdownOn) {
// 			$arena->lockdownOn = false;
// 		}
// 		// find all blocks
// 		$player->sendMessage ( TextFormat::YELLOW . "[BH] processing unlock arena....please wait!" );
// 		if (isset ( $plugin->cacheBlocks [$arenaName] )) {
// 			$cacheBlocks = $plugin->cacheBlocks [$arenaName];
// 			for($i = 0; $i < count ( $cacheBlocks ); $i ++) {
// 				unset ( $cacheBlocks [$i] );
// 			}
// 			unset ( $plugin->cacheBlocks [$arenaName] );
// 		}
// 		$arena->save ( $plugin->getDataFolder () );
// 		$plugin->arenaManager->playArenas [$arenaName] = $arena;
// 		$player->sendMessage ( TextFormat::GREEN . "[BH] Arena unlocked in " . round ( (microtime ( true ) - $startTime) ) . "  seconds" );
// 		return true;
// 	}
	
	public static function backupPlotBlocks(BlockHuntPlugIn $plugin, Player $player, $arenaName) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorinized to use this command." );
			return;
		}
		$startTime = microtime ( true );
		if (! isset ( $plugin->arenaManager->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] arena not found!" );
			return;
		}
		$arena = $plugin->arenaManager->playArenas [$arenaName];
		// find all blocks
		$output = "";
		$blocks = [ ];
		$player->sendMessage ( TextFormat::YELLOW . "[BH] processing backup....please wait!" );
		if ($arena instanceof ArenaModel) {
			if (! empty ( $arena->pos1 ) and ! empty ( $arena->pos2 )) {
				$arena->resetBackUpArenaHeight = empty($arena->resetBackUpArenaHeight)?5:$arena->resetBackUpArenaHeight;
				$blocks = self::getPlotBlocks ( $arena->pos1, $arena->pos2, true, $output, $arena->resetBackUpArenaHeight);
			}
		}
		if (count ( $blocks ) > 0) {
			$s = serialize ( $blocks );
			$path = $plugin->getDataFolder ();
			if (! file_exists ( $path )) {
				@mkdir ( $path, 0755, true );
			}
			file_put_contents ( $plugin->getDataFolder () . $arenaName . ".bak", $s );			
			$plugin->cacheBlocks [$arenaName] = $blocks;			
			$player->sendMessage ( TextFormat::GRAY . "[BH] " . $output );
			$player->sendMessage ( TextFormat::GREEN . "[BH] arena backup completed in " . round ( (microtime ( true ) - $startTime) ) . "  seconds" );
			unset ( $s );
		} else {
			$player->sendMessage ( TextFormat::GREEN . "[BH] arena backup failed - empty arena blocks" );
		}
		unset ( $blocks );
		return true;
	}
		
	public static function backupArenaBlocks(BlockHuntPlugIn $plugin, ArenaModel $arena) {
		$startTime = microtime ( true );
		// find all blocks
		$output = "";
		$blocks = [ ];
		$plugin->getLogger ()->info  ( TextFormat::YELLOW . "[BH] processing backup....please wait!" );
		if (! empty ( $arena->pos1 ) and ! empty ( $arena->pos2 )) {
			$arena->resetBackUpArenaHeight = empty($arena->resetBackUpArenaHeight)?5:$arena->resetBackUpArenaHeight;
			$blocks = self::getPlotBlocks ($arena->pos1, $arena->pos2, true, $output, $arena->resetBackUpArenaHeight);
		}
		if (count ( $blocks ) > 0) {
			$s = serialize ( $blocks );
			$path = $plugin->getDataFolder ();
			if (! file_exists ( $path )) {
				@mkdir ( $path, 0755, true );
			}
			file_put_contents ( $plugin->getDataFolder () . $arena->name . ".bak", $s );
			//$plugin->getLogger ()->info  ( TextFormat::GRAY . "[BH] " . $output );
			$plugin->getLogger ()->info  ( TextFormat::GREEN . "[BH] arena ".count($blocks)."] backup completed in " . round ( (microtime ( true ) - $startTime) ) . "  seconds" );
			unset ( $s );
		} else {
			$plugin->getLogger ()->info ( TextFormat::GREEN . "[BH] arena backup failed - empty arena blocks" );
		}
		unset ( $blocks );
		return true;
	}
	
	public static function restorePlotBlocks(BlockHuntPlugIn $plugin, Player $player, $arenaName) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorinized to use this command." );
			return;
		}
		if (! isset ( $plugin->arenaManager->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] arena not found!" );
			return;
		}
		$startTime = microtime ( true );
		$arena = $plugin->arenaManager->playArenas [$arenaName];
		$path = $plugin->getDataFolder ();
		if (! file_exists ( $path )) {
			@mkdir ( $path, 0755, true );
		}
		// find all blocks
		$output = "";
		$s = file_get_contents ( $plugin->getDataFolder () . $arenaName . ".bak" );
		if (empty ( $s )) {
			$player->sendMessage ( TextFormat::YELLOW . "[BH] arena backup copy not found!" );
			return;
		}
		$blocks = unserialize ( $s );
		$output = "";
		$player->sendMessage ( TextFormat::YELLOW . "[BH] Processing restore....please wait!" );
		self::setArenaBlocks ( $player, $blocks, $output );
		$player->sendMessage ( TextFormat::GREEN . "[BH] arena restore completed in " . round ( (microtime ( true ) - $startTime) ) . " seconds" );
		return true;
	}
	
	public static function resetArenaBlocks(BlockHuntPlugIn $plugin, ArenaModel $arena) {
		$startTime = microtime ( true );
		$blocks = [ ];
		if (! isset ( $plugin->cacheBlocks [$arena->name] )) {
			$path = $plugin->getDataFolder ();
			if (! file_exists ( $path )) {
				@mkdir ( $path, 0755, true );
			}
			//check if file exist
			$bakfileName = $plugin->getDataFolder () . $arena->name . ".bak";
			if (!file_exists($bakfileName)) {
				self::backupArenaBlocks($plugin, $arena);
			}			
			// find all blocks
			$output = "";
			$s = file_get_contents ( $bakfileName);
			if (empty ( $s )) {
				$plugin->getLogger ()->info  ( TextFormat::YELLOW . "[BH] arena [" . $arena->name . "] reset copy not found!" );
				return;
			}
			$blocks = unserialize ( $s );
			$plugin->cacheBlocks [$arena->name] = $blocks;
		}
		$blocks = $plugin->cacheBlocks [$arena->name];
		$output = "";
		$plugin->getLogger ()->info ( TextFormat::YELLOW . "[BH] Processing restore....please wait!" );
		self::repairArenaBlocks ( $arena->level, $blocks, $output );
		$plugin->getLogger ()->info ( TextFormat::GREEN . "[BH] arena [" . $arena->name . "] restore completed in " . round ( (microtime ( true ) - $startTime) ) . " seconds" );
		return true;
	}
	
	public static function repairArena(BlockHuntPlugIn $plugin, ArenaModel $arena) {
		$startTime = microtime ( true );
		
		$output = "";
		$plugin->getLogger ()->info ( TextFormat::YELLOW . "[BH] processing arena " . $arena->name . " repair ...." );
		if (! empty ( $arena->cacheBlocks ) && count ( $arena->cacheBlocks ) > 0) {
			$plugin->getLogger ()->info ( TextFormat::YELLOW . "[BH] scanning arena blocks" );
			self::repairArenaBlocks ( $arena->level, $arena->cacheBlocks, $output );
			$plugin->getLogger ()->info ( "[BH] " . $output );
			$plugin->getLogger ()->info ( "[BH] arena repair completed in " . round ( (microtime ( true ) - $startTime) ) . "  seconds" );
		} 
	}
	public static function repairArenaBlocks(Level $level, $blocks = array(), &$output = null) {
		$count = 0;
		foreach ( $blocks as $pblock ) {
			if ($pblock instanceof ArenaBlock) {
				$bid = $level->getBlockIdAt ( $pblock->x, $pblock->y, $pblock->z );
				if ($bid != $pblock->id) {
					$xblock = Item::get ( $pblock->id, $pblock->damage )->getBlock ();
					$level->setBlock ( new Position ( $pblock->x, $pblock->y, $pblock->z, $level ), $xblock, true, false );
					$count ++;
				}
			}
		}
		$output .= "$count block(s) have been restored.\n";
	}
	public static function setArenaBlocks(Player $player, $blocks = array(), &$output = null) {
		$count = 0;
		foreach ( $blocks as $pblock ) {
			if ($pblock instanceof ArenaBlock) {
				$bid = $player->getLevel ()->getBlockIdAt ( $pblock->x, $pblock->y, $pblock->z );
				if ($bid != $pblock->id) {
					$xblock = Item::get ( $pblock->id, $pblock->damage )->getBlock ();
					$player->getLevel ()->setBlock ( new Position ( $pblock->x, $pblock->y, $pblock->z, $player->getLevel () ), $xblock, true, false );
					$count ++;
				}
			}
		}
		$output .= "$count block(s) have been restored.\n";
	}
	public static function getPlotBlocks(Position $p1, Position $p2, $includeAirBlock = false, &$output = null, $backupArenaHeight=5) {
		$blocks = [ ];
		$send = false;
		$level = $p1->getLevel ();
		$bcnt = 1;
		$startX = min ( $p1->x, $p2->x );
		$endX = max ( $p1->x, $p2->x );
		$startY = min ( $p1->y, $p2->y );
		$endY = max ( $p1->y, $p2->y );
		$startZ = min ( $p1->z, $p2->z );
		$endZ = max ( $p1->z, $p2->z );
		$count = 0;
		//optimization reduce heigh to 5 
		if ($backupArenaHeight <= 0) {
			$backupArenaHeight = 5;
		}
		if ($backupArenaHeight>127) {
			$backupArenaHeight = 10;			
		}
		$endY = $startY + $backupArenaHeight;		
		for($x = $startX; $x <= $endX; ++ $x) {
			for($y = $startY; $y <= $endY; ++ $y) {
				for($z = $startZ; $z <= $endZ; ++ $z) {
					$bid = $level->getBlock ( new Position ( $x, $y, $z, $level ) );
					// exclude air save space
					if ($includeAirBlock) {
						$count ++;
						$pblock = new ArenaBlock ( $bid->getId (), $bid->getDamage (), $x, $y, $z );
						$key = $x . "." . $y . "." . $z;
						$blocks [$key] = $pblock;
					} else {
						if ($bid->getId () != Item::AIR) {
							$count ++;
							$pblock = new ArenaBlock ( $bid->getId (), $bid->getDamage (), $x, $y, $z );
							$key = $x . "." . $y . "." . $z;
							$blocks [$key] = $pblock;
						}
					}
				}
			}
		}
		$output .= "$count block(s) have been backup.\n";
		return $blocks;
	}
}