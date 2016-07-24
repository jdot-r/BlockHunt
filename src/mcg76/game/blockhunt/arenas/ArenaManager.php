<?php

namespace mcg76\game\blockhunt\arenas;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\Player;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\MiniGameBase;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\sound\PopSound;
use mcg76\game\blockhunt\utils\PortalManager;
use mcg76\game\blockhunt\BlockHuntController;
use pocketmine\block\Block;

/**
 * MCPE BlockHunt Minigame - Made by minecraftgenius76
 *
 * You're allowed to use for own usage only "as-is".
 * you're not allowed to republish or resell or for any commercial purpose.
 *
 * Thanks for your cooperate!
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76
 *        
 */
/**
 * MCG76 Arena Manager
 */
class ArenaManager extends MiniGameBase {
	const COMMAND_ARENA_SIGN_STAT = "setSignStat";
	const COMMAND_ARENA_SIGN_JOIN = "setSignJoin";
	const COMMAND_ARENA_POSITION = "setArenaPos";
	const COMMAND_ARENA_SEEKER_DOOR = "setArenaSeekerDoorPos";
	const COMMAND_ARENA_POS1 = "setPos1";
	const COMMAND_ARENA_POS2 = "setPos2";
	const COMMAND_ARENA_ENTRANCE = "setArenaEnter";
	const COMMAND_ARENA_EXIT = "setArenaExit";
	const COMMAND_ARENA_NEW = "newarena";
	const COMMAND_ARENA_ADD_BLOCK = "addblock";
	// player roles
	const PLAYER_ROLE_SEEKER = "seeker";
	const PLAYER_ROLE_HIDER = "hider";
	const PLAYER_ROLE_RANDOM = "role_random";
	public $playArenas = [ ];
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
			$instance = new ArenaManager ( $plugin );
		}
		return $instance;
	}
	
	/**
	 * preloading arena data
	 */
	public function loadArenas() {
		$path = $this->getPlugin ()->getDataFolder () . ArenaModel::ARENA_DIR;
		if (! file_exists ( $path )) {
			@mkdir ( $path, 0755, true );
			// $resource = new \SplFileInfo($file_name);
			foreach ( $this->plugin->getResources () as $resource ) {
				if (! $resource->isDir ()) {
					$fp = $resource->getPathname ();
					if (strpos ( $fp, "arena" ) !== false) {
						$this->log ( " *** setup default arena : " . $resource->getFilename () );
						copy ( $resource->getPathname (), $path . $resource->getFilename () );
					}
				}
			}
		}
		
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			if ($filename != "." && $filename != "..") {
				$data = new Config ( $path . $filename, Config::YAML );
				$this->log ( "load item-cases file:" . $path . $filename );
				$data->getAll ();
				Server::getInstance ()->loadLevel ( $data->get ( "levelName" ) );
				$pLevel = Server::getInstance ()->getLevelByName ( $data->get ( "levelName" ) );
				
				$name = str_replace ( ".yml", "", $filename );
				$levelName = $data->get ( "levelName" );
				$position = new Position ( $data->get ( "positionX" ), $data->get ( "positionY" ), $data->get ( "positionZ" ) );
				
				$id = $data->get ( "id" );
				$name = $data->get ( "name" );
				$type = $data->get ( "type" );
				$capacity = $data->get ( "capacity", 20 );
				$reward = $data->get ( "reward", 12 );
				$reset_time = $data->get ( "reset_time", 300 );
				$size = $data->get ( "size", 20 );
				
				$arena = new ArenaModel ( $name, $levelName, $position );
				$arena->id = $id;
				$arena->type = $type;
				$arena->reward = $reward;
				$arena->reset_time = $reset_time;
				$arena->capacity = $capacity;
				$arena->size = $size;
				$arena->count_down = $data->get ( "count_down", 10 );
				$arena->blocks = $data->get ( "blocks" );
				$arena->min = $data->get ( "min", 2 );
				$arena->max = $data->get ( "max", 20 );
				$arena->level = $pLevel;
				$arena->levelName = $levelName;
				// $arena->signStats = new Position ( $bx2, $by2, $bz2 );
				// $arena->signJoin = new Position ( $bx, $by, $bz );
				
				$kx = $data->get ( "entranceX" );
				$ky = $data->get ( "entranceY" );
				$kz = $data->get ( "entranceZ" );
				$arena->entrance = new Position ( $kx, $ky, $kz );
				
				$kx = $data->get ( "exitX" );
				$ky = $data->get ( "exitY" );
				$kz = $data->get ( "exitZ" );
				$arena->exit = new Position ( $kx, $ky, $kz );
				
				$bx = $data->get ( "seekerX" );
				$by = $data->get ( "seekerY" );
				$bz = $data->get ( "seekerZ" );
				$arena->seeker_warp = new Position ( $bx, $by, $bz );
				
				$bx = $data->get ( "hiderX" );
				$by = $data->get ( "hiderY" );
				$bz = $data->get ( "hiderZ" );
				$arena->hider_warp = new Position ( $bx, $by, $bz );
				
				$kx = $data->get ( "lobbyX" );
				$ky = $data->get ( "lobbyY" );
				$kz = $data->get ( "lobbyZ" );
				$arena->lobby = new Position ( $kx, $ky, $kz );
				
				$bx = $data->get ( "signJoinX" );
				$by = $data->get ( "signJoinY" );
				$bz = $data->get ( "signJoinZ" );
				$arena->signJoin = new Position ( $bx, $by, $bz );
				
				$bx2 = $data->get ( "signStatsX" );
				$by2 = $data->get ( "signStatsY" );
				$bz2 = $data->get ( "signStatsZ" );
				$arena->signStats = new Position ( $bx2, $by2, $bz2 );
				
				$bx2 = $data->get ( "signStats2X" );
				$by2 = $data->get ( "signStats2Y" );
				$bz2 = $data->get ( "signStats2Z" );
				$arena->signStats2 = new Position ( $bx2, $by2, $bz2 );
				
				$bx = $data->get ( "signExitX" );
				$by = $data->get ( "signExitY" );
				$bz = $data->get ( "signExitZ" );
				$arena->signExit = new Position ( $bx, $by, $bz );
				
				$bx = $data->get ( "signExit2X" );
				$by = $data->get ( "signExit2Y" );
				$bz = $data->get ( "signExit2Z" );
				$arena->signExit2 = new Position ( $bx, $by, $bz );
				
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
				$arena->seekergate1 = new Position ( $bx, $by, $bz, $pLevel );
				
				$bx = $data->get ( "seekergate2X" );
				$by = $data->get ( "seekergate2Y" );
				$bz = $data->get ( "seekergate2Z" );
				$arena->seekergate2 = new Position ( $bx, $by, $bz, $pLevel );
				
				$kx = $data->get ( "pos1X" );
				$ky = $data->get ( "pos1Y" );
				$kz = $data->get ( "pos1Z" );
				$arena->pos1 = new Position ( $kx, $ky, $kz, $pLevel );
				
				$bx = $data->get ( "pos2X" );
				$by = $data->get ( "pos2Y" );
				$bz = $data->get ( "pos2Z" );
				$arena->pos2 = new Position ( $bx, $by, $bz, $pLevel );
				
				$pkx = $data->get ( "portal1X" );
				$pky = $data->get ( "portal1Y" );
				$pkz = $data->get ( "portal1Z" );
				$arena->portalPos1 = new Position ( $pkx, $pky, $pkz, $pLevel );
				
				$pbx = $data->get ( "portal2X" );
				$pby = $data->get ( "portal2Y" );
				$pbz = $data->get ( "portal2Z" );
				$arena->portalPos2 = new Position ( $pbx, $pby, $pbz, $pLevel );
				
				$arena->allowSeekers = $data->get ( "allowSeekers", 10 );
				$arena->allowHiders = $data->get ( "allowHiders", 10 );
				$arena->seekerReleaseTime = $data->get ( "seekerReleaseTime", 30 );
				$arena->reminderCountDown = $data->get ( "reminderCountDown", 25 );
				$arena->active = $data->get ( "active", true );				
				//$arena->lockdownOn = $data->get ( "lockdownOn", true );
				$arena->resetBackUpArenaHeight = $data->get ( "reset_backup_arena_height", 5);
				$arena->resetNewGame = $data->get ( "reset_new_game", true);
				$arena->resetCountDown = $data->get ( "reset_count_down", 5);						
				$this->playArenas [$name] = $arena;
			}
		}
		closedir ( $handler );
	}
	public static function listsArenas(Player $sender, $path) {
		$xpath = $path . ArenaModel::ARENA_DIR;
		if (! file_exists ( $xpath )) {
			@mkdir ( $xpath, 0777, true );
			return null;
		}
		$output = "List of Arenas:\n";
		$handler = opendir ( $xpath );
		$i = 1;
		while ( ($filename = readdir ( $handler )) !== false ) {
			if ($filename != "." && $filename != "..") {
				$data = new Config ( $xpath . $filename, Config::YAML );
				$name = str_replace ( ".yml", "", $filename );
				$id = $data->get ( "id" );
				$levelname = $data->get ( "levelName" );
				$pos = new Position ( $data->get ( "positionX" ), $data->get ( "positionY" ), $data->get ( "positionZ" ) );
				$output .= $i . ". " . $name . " at " . $pos->x . " " . $pos->y . " " . $pos->z . "\n";
				$i ++;
			}
		}
		closedir ( $handler );
		return $output;
	}
	public function &session(Player $sender) {
		if (! isset ( $this->plugin->sessions [$sender->getName ()] )) {
			$this->plugin->sessions [$sender->getName ()] = array (
					"selection" => array (
							false,
							false 
					),
					"arena-name" => false,
					"arena-type" => false,
					"action" => false,
					"wand-usage" => false,
					"edit-mode" => false 
			);
		}
		return $this->plugin->sessions [$sender->getName ()];
	}
	public function setPosition1(&$session, Position $position, &$output) {
		$output = "";
		$session ["selection"] [0] = array (
				round ( $position->x ),
				round ( $position->y ),
				round ( $position->z ),
				$position->level 
		);
		$count = $this->countBlocks ( $session ["selection"] );
		if ($count === false) {
			$count = "";
		} else {
			$count = " ($count)";
		}
		$output .= "First position set to (" . $session ["selection"] [0] [0] . ", " . $session ["selection"] [0] [1] . ", " . $session ["selection"] [0] [2] . ")$count.\n";
		return true;
	}
	public function setPosition2(&$session, Position $position, &$output) {
		$output = "";
		$session ["selection"] [1] = array (
				round ( $position->x ),
				round ( $position->y ),
				round ( $position->z ),
				$position->level 
		);
		$count = $this->countBlocks ( $session ["selection"] );
		if ($count === false) {
			$count = "";
		} else {
			$count = " ($count)";
		}
		$output .= "Second position set to (" . $session ["selection"] [1] [0] . ", " . $session ["selection"] [1] [1] . ", " . $session ["selection"] [1] [2] . ")$count.\n";
		return true;
	}
	private function countBlocks($selection, &$startX = null, &$startY = null, &$startZ = null) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			return false;
		}
		$startX = min ( $selection [0] [0], $selection [1] [0] );
		$endX = max ( $selection [0] [0], $selection [1] [0] );
		$startY = min ( $selection [0] [1], $selection [1] [1] );
		$endY = max ( $selection [0] [1], $selection [1] [1] );
		$startZ = min ( $selection [0] [2], $selection [1] [2] );
		$endZ = max ( $selection [0] [2], $selection [1] [2] );
		return ($endX - $startX + 1) * ($endY - $startY + 1) * ($endZ - $startZ + 1);
	}
	public static function lengthSq($x, $y, $z) {
		return ($x * $x) + ($y * $y) + ($z * $z);
	}
	public function createArena(Player $sender, array $args) {
		if (! $sender->isOp ()) {
			$sender->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (count ( $args ) != 2) {
			$sender->sendMessage ( "[BH] Usage:/bh newarena [name]" );
			return;
		}
		$sender->sendMessage ( "[BH] Creating new Arena" );
		$defenceName = $args [1];
		if (isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$defenceName] )) {
			$sender->sendMessage ( "[BH] Warning! arena ALREADY Exist!. please use another name!" );
			return;
		}
		
		$name = $args [1];
		$position = $sender->getPosition ();
		$leveName = $sender->level->getName ();
		$newArena = new ArenaModel ( $name, $leveName, $position );
		$newArena->save ( $this->getPlugin ()->getDataFolder () );
		$this->getPlugin ()->getArenaManager ()->playArenas [$name] = $newArena;
		$sender->sendMessage ( "[BH] New Arena Saved!" );
		// clear selection
		$this->handleDeSelCommand ( $sender );
		return;
	}
	public function handlePos1Command($sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$this->setPosition1 ( $session, new Position ( $sender->x - 0.5, $sender->y, $sender->z - 0.5, $sender->getLevel () ), $output );
		$sender->sendMessage ( $output );
	}
	public function handlePos2Command($sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$this->setPosition2 ( $session, new Position ( $sender->x - 0.5, $sender->y, $sender->z - 0.5, $sender->getLevel () ), $output );
		$sender->sendMessage ( $output );
	}
	public function handleWandCommand(Player $player, $args) {
		$session = & $this->session ( $player );
		$this->handleDeSelCommand ( $player );
		$session ["wand-usage"] = true;
		$player->sendMessage ( "Wand selected" );
		if ($player->getInventory ()->getItemInHand ()->getId () != 292) {
			$player->getInventory ()->setItemInHand ( new Item ( 292 ) );
		}
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh wand [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$args [1]] )) {
			$output = "[BH] Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		$this->getPlugin ()->setupModeAction = self::COMMAND_ARENA_POSITION;
		$this->getPlugin ()->setupModeData = $arenaName;
		$player->sendMessage ( "[BH] Break a block to set the #1 position, place for the #1.\n" );
	}
	public function handleWandSeekerDoorCommand(Player $player, $args) {
		$session = & $this->session ( $player );
		$this->handleDeSelCommand ( $player );
		$session ["wand-usage"] = true;
		$player->sendMessage ( "Wand selected" );
		if ($player->getInventory ()->getItemInHand ()->getId () != 292) {
			$player->getInventory ()->setItemInHand ( new Item ( 292 ) );
		}
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh wandseekerdoor [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$args [1]] )) {
			$output = "[BH] Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		$this->getPlugin ()->setupModeAction = self::COMMAND_ARENA_SEEKER_DOOR;
		$this->getPlugin ()->setupModeData = $arenaName;
		$player->sendMessage ( "[BH] Set seeker door. Break a block to set the #1 position, place for the #1.\n" );
	}
	public function handleAddBlockCommand(Player $player, $args) {
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh addblock [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		$session = & $this->session ( $player );
		$session ["edit-mode"] = true;
		$player->sendMessage ( "Wand selected" );
		if ($player->getInventory ()->getItemInHand ()->getId () != 292) {
			$player->getInventory ()->setItemInHand ( new Item ( 292 ) );
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$output = "[BH] Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$session ["arena-name"] = $arenaName;
		$session ["action"] = self::COMMAND_ARENA_ADD_BLOCK;
		$this->getPlugin ()->setupModeAction = self::COMMAND_ARENA_ADD_BLOCK;
		$this->getPlugin ()->setupModeData = $arenaName;
		$player->sendMessage ( "[BH] Break a block set arena supported block.\n" );
	}
	public function handleDeSelCommand($sender) {
		$session = & $this->session ( $sender );
		$session ["selection"] = array (
				false,
				false 
		);
		unset ( $session ["wand-pos1"] );
		unset ( $session ["wand-pos2"] );
		unset ( $session ["arena-name"] );
		unset ( $session ["action"] );
		// also clear these two
		$this->getPlugin ()->setupModeAction = "";
		$this->getPlugin ()->setupModeData = "";
		
		$output = "Selection cleared.\n";
		$sender->sendMessage ( $output );
	}
	public function handleSetArenaEntranceCommand(Player $player, $args) {
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh setenter [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$output = "[BH] Arena Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->entrance = $player->getPosition ();
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Entrance] set to position :" . round ( $arena->entrance->x ) . " " . round ( $arena->entrance->y ) . " " . round ( $arena->entrance->z ) );
	}
	public function handleSetWallsCommand(Player $player, $args) {
		$output = "[BH] ";
		if (count ( $args ) != 3) {
			$output = "[BH] Usage: /bh setwall [arena name] [blockid].\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		$blockid = $args [2];
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$output = "[BH] Arena Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$block = Item::get ( $blockid )->getBlock ();
		if ($block == null) {
			$output = "[BH] Invalid block id.\n";
			$player->sendMessage ( $output );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$this->getPlugin ()->arenaManager->setWall ( $arena->pos1, $arena->pos1, $block, $output );
		$player->sendMessage ( "[BH] arena wall modified - " . $output );
	}
	public function handleSetArenaExitCommand(Player $player, $args) {
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh setexit [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$output = "[BH] Arena Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->exit = $player->getPosition ();
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Exit] set to position :" . round ( $arena->exit->x ) . " " . round ( $arena->exit->y ) . " " . round ( $arena->exit->z ) );
	}
	public function handleSetArenaHiderWarpCommand(Player $player, $args) {
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh sethider [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$output = "[BH] Arena Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->hider_warp = $player->getPosition ();
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [hider warp] set to position :" . round ( $arena->hider_warp->x ) . " " . round ( $arena->hider_warp->y ) . " " . round ( $arena->hider_warp->z ) );
	}
	public function handleSetArenaSeekerWarpCommand(Player $player, $args) {
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh setseeker [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$output = "[BH] Arena Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->seeker_warp = $player->getPosition ();
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [seeker warp] set to position :" . round ( $arena->seeker_warp->x ) . " " . round ( $arena->seeker_warp->y ) . " " . round ( $arena->seeker_warp->z ) );
	}
	public function handleSetLobbyCommand(Player $player, $args) {
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh setlobby [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		$arenaName = $args [1];
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$output = "[BH] Arena Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->lobby = $player->getPosition ();
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		
		// update the server setting in
		$this->getPlugin ()->getConfig ()->set ( "blockhunt_home_world", $player->level->getName () );
		$this->getPlugin ()->getConfig ()->set ( "blockhunt_home_x", $arena->lobby->x );
		$this->getPlugin ()->getConfig ()->set ( "blockhunt_home_y", $arena->lobby->y );
		$this->getPlugin ()->getConfig ()->set ( "blockhunt_home_z", $arena->lobby->z );
		$this->getPlugin ()->getConfig ()->save ();
		
		$player->sendMessage ( "[BH] arena [lobby] set to position :" . round ( $arena->lobby->x ) . " " . round ( $arena->lobby->y ) . " " . round ( $arena->lobby->z ) );
	}
	public function handleSetServerLobbyCommand(Player $player, $args) {
		if (count ( $args ) != 1) {
			$output = "[BH] Usage: /bh setserverlobby\n";
			$player->sendMessage ( $output );
			return;
		}
		// update the server setting in
		$this->getPlugin ()->getConfig ()->set ( "server_lobby_world", $player->level->getName () );
		$this->getPlugin ()->getConfig ()->set ( "server_lobby_x", $player->x );
		$this->getPlugin ()->getConfig ()->set ( "server_lobby_y", $player->y );
		$this->getPlugin ()->getConfig ()->set ( "server_lobby_z", $player->z );
		$this->getPlugin ()->getConfig ()->save ();
		
		$player->sendMessage ( "[BH] Server [lobby] set to [" . round ( $player->x ) . " " . round ( $player->y ) . " " . round ( $player->z ) . "]" );
	}
	public function handleSetSignJoin(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signJoin = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Join Sign] set to position :" . round ( $arena->signJoin->x ) . " " . round ( $arena->signJoin->y ) . " " . round ( $arena->signJoin->z ) );
	}
	public function handleSetSignJoinSeeker(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signJoinSeeker = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Seeker Join Sign] set to position :" . round ( $arena->signJoinSeeker->x ) . " " . round ( $arena->signJoinSeeker->y ) . " " . round ( $arena->signJoinSeeker->z ) );
	}
	public function handleSetSignJoinSeeker2(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signJoinSeeker2 = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Seeker Join Sign] set to position :" . round ( $arena->signJoinSeeker->x ) . " " . round ( $arena->signJoinSeeker->y ) . " " . round ( $arena->signJoinSeeker->z ) );
	}
	public function handleSetSignJoinHider(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signJoinHider = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Hider Join Sign] set to position :" . round ( $arena->signJoin->x ) . " " . round ( $arena->signJoin->y ) . " " . round ( $arena->signJoin->z ) );
	}
	public function handleSetSignJoinHider2(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signJoinHider2 = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Hider Join Sign] set to position :" . round ( $arena->signJoin->x ) . " " . round ( $arena->signJoin->y ) . " " . round ( $arena->signJoin->z ) );
	}
	public function handleSetSignExit(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signExit = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Exit Sign] set to position :" . round ( $arena->signExit->x ) . " " . round ( $arena->signExit->y ) . " " . round ( $arena->signExit->z ) );
	}
	public function handleSetSignExit2(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signExit2 = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Exit Sign] set to position :" . round ( $arena->signExit->x ) . " " . round ( $arena->signExit->y ) . " " . round ( $arena->signExit->z ) );
	}
	public function handleSetSignStats(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signStats = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Stat Sign] set to position :" . round ( $arena->signStats->x ) . " " . round ( $arena->signStats->y ) . " " . round ( $arena->signStats->z ) );
	}
	public function handleSetSignStats2(Player $player, $arenaName, $block) {
		if (! $player->isOp ()) {
			$player->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (! isset ( $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] )) {
			$player->sendMessage ( "[BH] Arena doesn't exist!" );
			return;
		}
		$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$arenaName];
		$arena->signStats2 = new Position ( $block->x, $block->y, $block->z );
		$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
		$arena->save ( $this->getPlugin ()->getDataFolder () );
		$player->sendMessage ( "[BH] arena [Stat Sign] set to position :" . round ( $arena->signStats->x ) . " " . round ( $arena->signStats->y ) . " " . round ( $arena->signStats->z ) );
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param string $b        	
	 * @return boolean
	 */
	public function handleTapOnArenaSigns(Player $player, $b) {
		$success = true;
		foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as &$arena ) {
			if ($arena instanceof ArenaModel) {
				
				// seeker
				if (! empty ( $arena->signJoinSeeker )) {
					$blockPosKey = round ( $b->x ) . "." . round ( $b->y ) . "." . round ( $b->z );
					$casePosKey = round ( $arena->signJoinSeeker->x ) . "." . round ( $arena->signJoinSeeker->y ) . "." . round ( $arena->signJoinSeeker->z );
					if ($blockPosKey === $casePosKey) {
						if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
							$player->sendTip ( TextFormat::YELLOW . "[BH] Game still on. please wait!" );
							return;
						}
						if ($this->isSeekersReachMax ( $arena )) {
							$player->sendMessage ( $this->getMsg ( "bh.play.warning.toomanyseekers" ) );
						} else {
							$arena->joinedplayers [$player->getName ()] = self::PLAYER_ROLE_SEEKER;
							$player->sendMessage ( TextFormat::BLUE . $this->getMsg ( "bh.play.warning.seekerwaitingforstart" ) );
							$player->teleport ( $arena->seeker_warp );
							$player->getLevel ()->addSound ( new LaunchSound ( $player->getPosition () ), array (
									$player 
							) );
							PortalManager::addParticles ( $player->getLevel (), new Position ( $player->x, $player->y + 1, $player->z, $player->getLevel () ), 300 );
						}
						break;
					}
				}
				if (! empty ( $arena->signJoinSeeker2 )) {
					$blockPosKey = round ( $b->x ) . "." . round ( $b->y ) . "." . round ( $b->z );
					$casePosKey = round ( $arena->signJoinSeeker2->x ) . "." . round ( $arena->signJoinSeeker2->y ) . "." . round ( $arena->signJoinSeeker2->z );
					if ($blockPosKey === $casePosKey) {
						if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
							$player->sendTip ( TextFormat::YELLOW . "[BH] Game still on. please wait!" );
							return;
						}
						if ($this->isSeekersReachMax ( $arena )) {
							$player->sendMessage ( $this->getMsg ( "bh.play.warning.toomanyseekers" ) );
						} else {
							$arena->joinedplayers [$player->getName ()] = self::PLAYER_ROLE_SEEKER;
							$player->sendMessage ( TextFormat::BLUE . $this->getMsg ( "bh.play.warning.seekerwaitingforstart" ) );
							$player->teleport ( $arena->seeker_warp );
							$player->getLevel ()->addSound ( new LaunchSound ( $player->getPosition () ), array (
									$player 
							) );
							PortalManager::addParticles ( $player->getLevel (), new Position ( $player->x, $player->y + 1, $player->z, $player->getLevel () ), 300 );
						}
						break;
					}
				}
				
				// hider
				if (! empty ( $arena->signJoinHider )) {
					$blockPosKey = round ( $b->x ) . "." . round ( $b->y ) . "." . round ( $b->z );
					$casePosKey = round ( $arena->signJoinHider->x ) . "." . round ( $arena->signJoinHider->y ) . "." . round ( $arena->signJoinHider->z );
					if ($blockPosKey === $casePosKey) {
						if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
							$player->sendTip ( TextFormat::YELLOW . "[BH] Game still on. please wait!" );
							return;
						}
						if ($this->isHiderReachMax ( $arena )) {
							$player->sendMessage ( $this->getMsg ( "bh.play.warning.toomanyhiders" ) );
						} else {
							$arena->joinedplayers [$player->getName ()] = self::PLAYER_ROLE_HIDER;
							$player->sendMessage ( TextFormat::AQUA . $this->getMsg ( "bh.play.warning.hiderwaitingforstart" ) );
							$player->teleport ( $arena->hider_warp );
							$player->getLevel ()->addSound ( new LaunchSound ( $player->getPosition () ), array (
									$player 
							) );
							PortalManager::addParticles ( $player->getLevel (), new Position ( $player->x, $player->y + 1, $player->z, $player->getLevel () ), 300 );
						}
						break;
					}
				}
				if (! empty ( $arena->signJoinHider2 )) {
					$blockPosKey = round ( $b->x ) . "." . round ( $b->y ) . "." . round ( $b->z );
					$casePosKey = round ( $arena->signJoinHider2->x ) . "." . round ( $arena->signJoinHider2->y ) . "." . round ( $arena->signJoinHider2->z );
					if ($blockPosKey === $casePosKey) {
						if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
							$player->sendTip ( TextFormat::YELLOW . "[BH] Game still on. please wait!" );
							return;
						}
						if ($this->isHiderReachMax ( $arena )) {
							$player->sendMessage ( $this->getMsg ( "bh.play.warning.toomanyhiders" ) );
						} else {
							$arena->joinedplayers [$player->getName ()] = self::PLAYER_ROLE_HIDER;
							$player->sendMessage ( TextFormat::AQUA . $this->getMsg ( "bh.play.warning.hiderwaitingforstart" ) );
							$player->teleport ( $arena->hider_warp );
							$player->getLevel ()->addSound ( new LaunchSound ( $player->getPosition () ), array (
									$player 
							) );
							PortalManager::addParticles ( $player->getLevel (), new Position ( $player->x, $player->y + 1, $player->z, $player->getLevel () ), 300 );
						}
						break;
					}
				}
				
				// exit
				if (! empty ( $arena->signExit )) {
					$blockPosKey = round ( $b->x ) . "." . round ( $b->y ) . "." . round ( $b->z );
					$casePosKey = round ( $arena->signExit->x ) . "." . round ( $arena->signExit->y ) . "." . round ( $arena->signExit->z );
					if ($blockPosKey === $casePosKey) {
						//echo "signExit1";
						$player->teleport ( $arena->lobby );
						$this->plugin->controller->handlePlayerLeavethePlay($player);						
						$player->sendMessage ( TextFormat::YELLOW.$this->getMsg ( "bh.play.warning.playerexit" ) );
						$player->getLevel ()->addSound ( new PopSound ( $player->getPosition () ), array (
								$player 
						) );
						unset ( $arena->joinedplayers [$player->getName ()] );
						unset ( $arena->seekers [$player->getName ()] );
						unset ( $arena->hidders [$player->getName ()] );						
						break;
					}
				}
				if (! empty ( $arena->signExit2 )) {
					$blockPosKey = round ( $b->x ) . "." . round ( $b->y ) . "." . round ( $b->z );
					$casePosKey = round ( $arena->signExit2->x ) . "." . round ( $arena->signExit2->y ) . "." . round ( $arena->signExit2->z );
					if ($blockPosKey === $casePosKey) {
						$player->teleport ( $arena->lobby );						
						$this->plugin->controller->handlePlayerLeavethePlay($player);
						$player->sendMessage ( TextFormat::YELLOW.$this->getMsg ( "bh.play.warning.playerexit" ) );
						$player->getLevel ()->addSound ( new PopSound ( $player->getPosition () ), array (
								$player 
						) );
						unset ( $arena->joinedplayers [$player->getName ()] );
						unset ( $arena->seekers [$player->getName ()] );
						unset ( $arena->hidders [$player->getName ()] );
						break;
					}
				}
				
				if (! empty ( $arena->signJoin )) {
					$blockPosKey = round ( $b->x ) . "." . round ( $b->y ) . "." . round ( $b->z );
					$casePosKey = round ( $arena->signJoin->x ) . "." . round ( $arena->signJoin->y ) . "." . round ( $arena->signJoin->z );
					if ($blockPosKey === $casePosKey) {
						if ($arena->status === ArenaModel::ARENA_STATUS_PLAYING) {
							$player->sendTip ( TextFormat::YELLOW . "[BH] Game still on. please wait!" );
							return;
						}
						if ($this->isArenaMax ( $arena )) {
							$player->sendMessage ( "[BH] arena has limit of [" . $arena->max . "] players. join back layer!" );
						} else {
							$player->getLevel ()->addSound ( new PopSound ( $player->getPosition () ), array (
									$player 
							) );
							$arena->joinedplayers [$player->getName ()] = self::PLAYER_ROLE_RANDOM;
							$player->sendMessage ( TextFormat::GRAY . "[BH] You have joined as " . TextFormat::GOLD . "[random pick role]" . TextFormat::GRAY . " player. please wait here for game start!" );
						}
						break;
					}
				}
			}
		}
		return $success;
	}
	
	/**
	 *
	 * @param ArenaModel $arena        	
	 * @return boolean
	 */
	public function isSeekersReachMax(ArenaModel $arena) {
		$joinedseekers = 0;
		foreach ( $arena->joinedplayers as $player => $role ) {
			if ($role == ArenaManager::PLAYER_ROLE_SEEKER) {
				$joinedseekers ++;
			}
		}
		
		$arena->allowSeekers = ($arena->allowSeekers == null || $arena->allowSeekers == 0) ? ($arena->max / 2) : $arena->allowSeekers;
		if ($joinedseekers == $arena->allowSeekers) {
			return true;
		}
		return false;
	}
	public function isHiderReachMax(ArenaModel $arena) {
		$joinedhiders = 0;
		foreach ( $arena->joinedplayers as $player => $role ) {
			if ($role == ArenaManager::PLAYER_ROLE_HIDER) {
				$joinedhiders ++;
			}
		}
		$arena->allowHiders = ($arena->allowHiders == null || $arena->allowHiders == 0) ? ($arena->max / 2) : $arena->allowHiders;
		if ($joinedhiders == $arena->allowHiders) {
			return true;
		}
		return false;
	}
	public function isArenaMax(ArenaModel $arena) {
		return count ( $arena->joinedplayers ) >= $arena->max ? true : false;
	}
	/**
	 *
	 * @param ArenaModel $arena        	
	 * @param Position $p1        	
	 * @param Position $p2        	
	 * @param unknown $block        	
	 * @param string $output        	
	 * @return boolean
	 */
	public function setGate(ArenaModel $arena, Position $p1, Position $p2, Block $block, &$output = null) {
		$level = $p1->getLevel ();
		if (empty ( $level )) {
			$level = $arena->level;
			echo "** empty level /n";
		}
		if (! empty ( $level )) {
			$startX = min ( $p1->x, $p2->x );
			$endX = max ( $p1->x, $p2->x );
			$startY = min ( $p1->y, $p2->y );
			$endY = max ( $p1->y, $p2->y );
			$startZ = min ( $p1->z, $p2->z );
			$endZ = max ( $p1->z, $p2->z );
			$count = 0;
			for($x = $startX; $x <= $endX; ++ $x) {
				for($y = $startY; $y <= $endY; ++ $y) {
					for($z = $startZ; $z <= $endZ; ++ $z) {
						$direct = false;
						$update = true;
                        if (isset($block)) {
                            $level->setBlock ( new Position ( $x, $y, $z, $level ), $block, $direct, $update );
                            $count ++;
                        }
					}
				}
			}
			$output = "$count block(s) have been updated.\n";
			return true;
		}
		return false;
	}
	
	public function setWall(Position $p1, Position $p2, $block, &$output = null) {
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
		for($x = $startX; $x <= $endX; ++ $x) {
			for($y = $startY; $y <= $endY; ++ $y) {
				for($z = $startZ; $z <= $endZ; ++ $z) {
					if ($x == $startX || $x == $endX || $z == $startZ || $z == $endZ) {
						$direct = false;
						$update = true;
						$level->setBlock ( new Position ( $x, $y, $z, $level ), $block, $direct, $update );
						$count ++;
					}
				}
			}
		}
		
		if ($send === false) {
			$forceSend = function ($X, $Y, $Z) {
				$this->changedCount [$X . ":" . $Y . ":" . $Z] = 4096;
			};
			$forceSend->bindTo ( $level, $level );
			for($X = $startX >> 4; $X <= ($endX >> 4); ++ $X) {
				for($Y = $startY >> 4; $Y <= ($endY >> 4); ++ $Y) {
					for($Z = $startZ >> 4; $Z <= ($endZ >> 4); ++ $Z) {
						$forceSend ( $X, $Y, $Z );
					}
				}
			}
		}
		$output .= "$count block(s) have been updated.\n";
		return true;
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param
	 *        	$b
	 * @internal param BlockBreakEvent $event
	 */
	public function handleBlockBreakSelection(Player $player, $b) {
		// $player = $event->getPlayer ();
		// $b = $event->getBlock ();
		$output = "[BH] ";
		if ($player instanceof Player) {
			if ($this->getPlugin ()->setupModeAction === ArenaManager::COMMAND_ARENA_ADD_BLOCK) {
				$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData];
				$arena->blocks [$b->getId ()] = $b->getName ();
				$arena->save ( $this->getPlugin ()->getDataFolder () );
				$player->sendMessage ( $this->getMsg ( "bh.play.setup.addarenablock" ) . " [" . $b->getId () . "] " . $b->getName () );
			}
		}
		
		if ($player instanceof Player) {
			if ($this->getPlugin ()->setupModeAction == ArenaManager::COMMAND_ARENA_POSITION) {
				$session = &$this->getPlugin ()->arenaManager->session ( $player );
				if ($session != null && $session ["wand-usage"] === true) {
					if (! isset ( $session ["wand-pos1"] ) || $session ["wand-pos1"] === null) {
						$session ["wand-pos1"] = $b;
						$this->getPlugin ()->arenaManager->setPosition1 ( $session, new Position ( $b->x - 0.5, $b->y, $b->z - 0.5, $player->getLevel () ), $output );
						// setup
						$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData];
						$arena->pos1 = new Position ( $b->x, $b->y, $b->z );
						$arena->save ( $this->getPlugin ()->getDataFolder () );
						$this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData] = $arena;
						$player->sendMessage ( $this->getMsg ( "bh.play.setup.setarenap1" ) . " [" . round ( $b->x ) . " " . round ( $b->y ) . " " . round ( $b->z ) );
						$player->sendMessage ( $output );
						return;
					}
					if (! isset ( $session ["wand-pos2"] ) || $session ["wand-pos2"] === null) {
						$session ["wand-pos2"] = $b;
						$this->getPlugin ()->arenaManager->setPosition2 ( $session, new Position ( $b->x - 0.5, $b->y, $b->z - 0.5, $player->getLevel () ), $output );
						// setup
						$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData];
						$arena->pos2 = new Position ( $b->x, $b->y, $b->z );
						$arena->save ( $this->getPlugin ()->getDataFolder () );
						$this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData] = $arena;
						$player->sendMessage ( $this->getMsg ( "bh.play.setup.setarenap2" ) . " [" . round ( $b->x ) . " " . round ( $b->y ) . " " . round ( $b->z ) );
						$this->getPlugin ()->setupModeAction = "";
						$this->getPlugin ()->setupModeData = "";
						$player->sendMessage ( $output );
						return;
					}
				}
			}
		}
		
		if ($player instanceof Player) {
			if ($this->getPlugin ()->setupModeAction === ArenaManager::COMMAND_ARENA_SEEKER_DOOR) {
				$session = &$this->getPlugin ()->arenaManager->session ( $player );
				if ($session != null && $session ["wand-usage"] === true) {
					if (! isset ( $session ["wand-pos1"] ) || $session ["wand-pos1"] === null) {
						$session ["wand-pos1"] = $b;
						$this->getPlugin ()->arenaManager->setPosition1 ( $session, new Position ( $b->x - 0.5, $b->y, $b->z - 0.5, $player->getLevel () ), $output );
						// setup
						$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData];
						$arena->seekergate1 = new Position ( $b->x, $b->y, $b->z );
						$arena->save ( $this->getPlugin ()->getDataFolder () );
						$this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData] = $arena;
						$player->sendMessage ( $this->getMsg ( "bh.play.setup.setseekerdoorp1" ) . " [" . round ( $b->x ) . " " . round ( $b->y ) . " " . round ( $b->z ) );
						$player->sendMessage ( $output );
						return;
					}
					if (! isset ( $session ["wand-pos2"] ) || $session ["wand-pos2"] === null) {
						$session ["wand-pos2"] = $b;
						$this->getPlugin ()->arenaManager->setPosition2 ( $session, new Position ( $b->x - 0.5, $b->y, $b->z - 0.5, $player->getLevel () ), $output );
						// setup
						$arena = $this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData];
						$arena->seekergate2 = new Position ( $b->x, $b->y, $b->z );
						$arena->save ( $this->getPlugin ()->getDataFolder () );
						$this->getPlugin ()->getArenaManager ()->playArenas [$this->getPlugin ()->setupModeData] = $arena;
						$player->sendMessage ( $this->getMsg ( "bh.play.setup.setseekerdoorp2" ) . " [" . round ( $b->x ) . " " . round ( $b->y ) . " " . round ( $b->z ) );
						$this->getPlugin ()->setupModeAction = "";
						$this->getPlugin ()->setupModeData = "";
						$player->sendMessage ( $output );
						return;
					}
				}
			}
		}
	}
	
	/**
	 * Recursively delete a directory
	 *
	 * @param string $dir
	 *        	Directory name
	 * @param boolean $deleteRootToo
	 *        	Delete specified top-level directory as well
	 */
	public function unlinkRecursive($dir, $deleteRootToo) {
		if (! $dh = @opendir ( $dir )) {
			return;
		}
		while ( false !== ($obj = readdir ( $dh )) ) {
			if ($obj == '.' || $obj == '..') {
				continue;
			}
			
			if (! @unlink ( $dir . '/' . $obj )) {
				$this->unlinkRecursive ( $dir . '/' . $obj, true );
			}
		}
		closedir ( $dh );
		if ($deleteRootToo) {
			@rmdir ( $dir );
		}
		return;
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