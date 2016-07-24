<?php

namespace mcg76\game\blockhunt\defence;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\OfflinePlayer;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\item\ItemBlock;
use pocketmine\item\Item;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\MiniGameBase;

/**
 * BlockHunt - Made by minecraftgenius76
 *
 * You're allowed to use for own usage only, you're not allowed to republish or resell or for any commercial purpose.
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
 * Defence Manager
 */
class DefenceManager extends MiniGameBase {
	const COMMAND_ARENA_SIGN_STAT = "setSignStat";
	const COMMAND_ARENA_SIGN_JOIN = "setSignJoin";
	const COMMAND_DEFENCE_POS1 = "setDefencePos1";
	const COMMAND_DEFENCE_POS2 = "setDefencePos2";
	const COMMAND_DEFENCE_POSITION = "setDefencePos";
	
	public $defences = [ ];
	
	/**
	 * 
	 * @param BlockHuntPlugIn $plugin
	 */
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
	public static function getInstance(BlockHuntPlugIn $plugin)
	{
		static $instance = null;
		if (null === $instance) {
			$instance = new DefenceManager($plugin);
		}
		return $instance;
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
	public function newDefence(Player $sender, array $args) {
		if (! $sender->isOp ()) {
			$sender->sendMessage ( "[BH] You are not authorized to use this command." );
			return;
		}
		if (count ( $args ) != 2) {
			$sender->sendMessage ( "[BH] Usage:/bh newdefence [name]" );
			return;
		}
		
		$sender->sendMessage ( "[BH] Creating new defence" );
		$defenceName = $args [1];
		if (isset ( $this->getPlugin ()->defences [$defenceName] )) {
			$sender->sendMessage ( "[BH] Warning! arena ALREADY Exist!. please use another name!" );
			return;
		}
		// $id = time();
		$name = $args [1];
		$position = $sender->getPosition ();
		$newDefence = new DefenceModel ( $name );
		$newDefence->levelName = $sender->level->getName ();
		$newDefence->save ( $this->getPlugin ()->getDataFolder () );
		$this->getPlugin ()->defences [$name] = $newDefence;
		$sender->sendMessage ( "[BH] New Defence Saved!" );
		// clear selection
		// $this->handleDeSelCommand ( $sender );
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
		// check player items on hand
		if ($player->getInventory ()->getItemInHand ()->getId () != 292) {
			$player->getInventory ()->setItemInHand ( new Item ( 292 ) );
		}
		if (count ( $args ) != 2) {
			$output = "[BH] Usage: /bh defencewand [arena name].\n";
			$player->sendMessage ( $output );
			return;
		}
		
		if (! isset ( $this->getPlugin ()->defences [$args [1]] )) {
			$output = "[BH] Name not found!.\n";
			$player->sendMessage ( $output );
			return;
		}
				
		$this->getPlugin ()->setupModeAction = self::COMMAND_DEFENCE_POSITION;
		$this->getPlugin ()->setupModeData = $args [1];
		$player->sendMessage ( "[BH] Break a block to set the #1 position, place for the #1.\n" );
	}
	public function handleDeSelCommand($sender) {
		$session = & $this->session ( $sender );
		$session ["selection"] = array (
				false,
				false 
		);
		unset ( $session ["wand-pos1"] );
		unset ( $session ["wand-pos2"] );
		$output = "Selection cleared.\n";
		$sender->sendMessage ( $output );
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
	private function hasCommandAccess(CommandSender $sender) {
		if ($sender->getName () == "CONSOLE") {
			return true;
		} elseif ($sender->isOp ()) {
			return true;
		}
		return false;
	}
	public function loadDefences() {
		$path = $this->getPlugin ()->getDataFolder () . DefenceModel::DEFENCE_DIR;
		if (! file_exists ( $path )) {
			@mkdir ( $path, 0755, true );
			// $resource = new \SplFileInfo($file_name);
			foreach ( $this->plugin->getResources () as $resource ) {
				if (! $resource->isDir ()) {
					$fp = $resource->getPathname ();
					if (strpos ( $fp, "defence" ) !== false) {
						$this->log ( " *** setup defence: " . $resource->getFilename () );
						copy ( $resource->getPathname (), $path . $resource->getFilename () );
					}
				}
			}
		}
				
		$this->log ( "#loading defences on " . $path );
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			$this->log ( $filename );
			if ($filename != "." && $filename != "..") {
				$data = new Config ( $path . $filename, Config::YAML );
				Server::getInstance ()->loadLevel ( $data->get ( "levelName" ) );
				$pLevel = Server::getInstance ()->getLevelByName ( $data->get ( "levelName" ) );
				// if (($pLevel = Server::getInstance ()->getLevelByName ( $data->get ( "levelName" ) )) === null)
				// continue;
				$name = str_replace ( ".yml", "", $filename );
				$p1 = new Position ( $data->get ( "point1X" ), $data->get ( "point1Y" ), $data->get ( "point1Z" ), $pLevel );
				$p2 = new Position ( $data->get ( "point2X" ), $data->get ( "point2Y" ), $data->get ( "point2Z" ), $pLevel );
				$entrance = new Position ( $data->get ( "entranceX" ), $data->get ( "entranceY" ), $data->get ( "entranceZ" ), $pLevel );
				$name = $data->get ( "name" );
				$type = $data->get ( "type" );
				$levelName = $data->get ( "levelName" );
				$effect = $data->get ( "effect" );
				
				$newdefence = new DefenceModel ( $name );
				$newdefence->effect = $effect;
				$newdefence->levelName = $levelName;
				$newdefence->p1 = $p1;
				$newdefence->p2 = $p2;
				$newdefence->entrance = $entrance;
				$this->defences [$name] = $newdefence;
			}
		}
		closedir ( $handler );
	}

    /**
     *
     * @param Player $player
     * @param        $b
     * @internal param BlockBreakEvent $event
     */
	public function handleBlockBreakSelection(Player $player, $b) {
        $output="[BH] ";
		if ($player instanceof Player) {
			if ($this->getPlugin ()->setupModeAction == DefenceManager::COMMAND_DEFENCE_POSITION) {
				$session = &$this->getPlugin ()->defenceManager->session ( $player );
				if ($session != null && $session ["wand-usage"] == true) {
					if (! isset ( $session ["wand-pos1"] ) || $session ["wand-pos1"] == null) {
						$session ["wand-pos1"] = $b;
						$this->getPlugin ()->defenceManager->setPosition1 ( $session, new Position ( $b->x - 0.5, $b->y, $b->z - 0.5, $player->getLevel () ), $output );
						// setup
						$defence = $this->getPlugin ()->defenceManager->defences [$this->getPlugin ()->setupModeData];
						$defence->p1 = new Position ( $b->x, $b->y, $b->z );
						$defence->save ( $this->getPlugin ()->getDataFolder () );
						$this->getPlugin ()->defenceManager->defences [$this->getPlugin ()->setupModeData] = $defence;
						$player->sendMessage ( "Set Defence Position #1 [" . round ( $b->x ) . " " . round ( $b->y ) . " " . round ( $b->z ));
						$player->sendMessage ( $output );
						return;
					}
					if (! isset ( $session ["wand-pos2"] ) || $session ["wand-pos2"] == null) {
						$session ["wand-pos2"] = $b;
						$this->getPlugin ()->defenceManager->setPosition2 ( $session, new Position ( $b->x - 0.5, $b->y, $b->z - 0.5, $player->getLevel () ), $output );
						// setup
						$defence = $this->getPlugin ()->defenceManager->defences [$this->getPlugin ()->setupModeData];
						$defence->p2 = new Position ( $b->x, $b->y, $b->z );
						$defence->save ( $this->getPlugin ()->getDataFolder () );
						$this->getPlugin ()->defenceManager->defences [$this->getPlugin ()->setupModeData] = $defence;
						$player->sendMessage ( "Set Defence Position #2 [" . round ( $b->x ) . " " . round ( $b->y ) . " " . round ( $b->z ) ) ;
						$this->getPlugin ()->setupModeAction = "";
						$this->getPlugin ()->setupModeData = "";
						$player->sendMessage ( $output );
						return;
					}
				}
			}
		}
	}
	public function listCachedDefences(Player $player) {
		$i = 1;
		foreach ( $this->getPlugin ()->defenceManager->defences as $arena ) {
			$player->sendMessage ( $i . "." . $arena->name );
			$i ++;
		}
	}
	
	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone()
	{
	}
	
	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup()
	{
	}
}