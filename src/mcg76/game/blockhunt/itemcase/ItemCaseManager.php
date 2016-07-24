<?php

namespace mcg76\game\blockhunt\itemcase;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\player\PlayerInteractEvent;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\MiniGameBase;

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
class ItemCaseManager extends MiniGameBase {
	const ITEMCASE_DIR = 'itemcase/';
	const ACTION_ADD_ITEM_CASE = "action_addcase";
	const ACTION_DEL_ITEM_CASE = "action_delcase";
	const ACTION_SET_ITEM_CASE = "action_setcase";
	const ACTION_NULL = "";
	const ACTION_SET_ITEM_CASE_LINK = "action_setcase_link";
	const ACTION_SET_ITEM_CASE_BUY = "action_setcase_buy";
	
	// item cases
	public $storeItemCases = [ ];
	public $storeItemCasesLinksPos = [ ];
	public $storeItemCasesBuyPos = [ ];
	public $npcsSpawns = [ ];
	
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
			$instance = new ItemCaseManager($plugin);
		}
	
		return $instance;
	}
	
	/**
	 */
	public function loadItemCases() {
		$path = $this->getPlugin ()->getDataFolder () . self::ITEMCASE_DIR;
		if (! file_exists ( $path )) {
			@mkdir ( $path, 0755, true );
			// $resource = new \SplFileInfo($file_name);
			foreach ( $this->plugin->getResources () as $resource ) {
				if (! $resource->isDir ()) {
					$fp = $resource->getPathname ();
					if (strpos ( $fp, "itemcase" ) !== false) {
						$this->log ( " *** setup default [item cases]: " . $resource->getFilename () );
						copy ( $resource->getPathname (), $path . $resource->getFilename () );
					}
				}
			}
		}
		
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			if ($filename != "." && $filename != "..") {
				$data = new Config ( $path . $filename, Config::YAML );
				$this->getPlugin ()->getLogger ()->info ( "load item-cases file:" . $path . $filename );
				$data->getAll ();
				Server::getInstance ()->loadLevel ( $data->get ( "levelName" ) );
				if (($pLevel = Server::getInstance ()->getLevelByName ( $data->get ( "levelName" ) )) === null) {
					$this->getPlugin ()->getLogger ()->info ( " error loading item-cases level :" . $data->get ( "levelName" ) );
					continue;
				}
				$name = str_replace ( ".yml", "", $filename );
				$levelname = $data->get ( "levelName" );
				
				$position = new Position ( $data->get ( "positionX" ), $data->get ( "positionY" ), $data->get ( "positionZ" ), $pLevel );
				
				$linkPos = null;
				$kx = $data->get ( "linkPosX" );
				$ky = $data->get ( "linkPosY" );
				$kz = $data->get ( "linkPosZ" );
				if ($kx != null && $ky != null && $kz != null) {
					$linkPos = new Position ( $kx, $ky, $kz );
				}
				$bx = $data->get ( "buyPosX" );
				$by = $data->get ( "buyPosY" );
				$bz = $data->get ( "buyPosZ" );
				$buyPos = new Position ( $bx, $by, $bz );
				
				$eid = $data->get ( "eid" );
				$itemId = $data->get ( "itemId" );
				$itemName = $data->get ( "itemName" );
				$itemQuantity = $data->get ( "quantity" );
				$itemStandBlockId = $data->get ( "itemStandBlockId" );
				$itemCoverBlockId = $data->get ( "itemCoverBlockId" );
				$price = $data->get ( "price" );
				$discount = $data->get ( "discount" );
				$coupon = $data->get ( "coupon" );
				$points = $data->get ( "points" );
				
				$itemCaseModel = new ItemCaseModel ( $eid, $name, $itemId, $itemQuantity, $price );
				$itemCaseModel->levelName = $levelname;
				$itemCaseModel->itemName = $itemName;
				$itemCaseModel->itemQuantity = $itemQuantity;
				$itemCaseModel->itemStandBlockId = $itemStandBlockId;
				$itemCaseModel->itemCoverBlockId = $itemCoverBlockId;
				$itemCaseModel->discount = $discount;
				$itemCaseModel->points = $points;
				$itemCaseModel->position = $position;
				$itemCaseModel->linkPos = $linkPos;
				$itemCaseModel->buyPos = $buyPos;
				
				$this->storeItemCases [$name] = $itemCaseModel;
				if ($itemCaseModel->buyPos != null) {
					$posKey = round ( $itemCaseModel->buyPos->x ) . "." . round ( $itemCaseModel->buyPos->y ) . "." . round ( $itemCaseModel->buyPos->z );
					$this->storeItemCasesBuyPos [$posKey] = $itemCaseModel;
				}
			}
		}
		closedir ( $handler );
		$this->getPlugin ()->getLogger ()->info ( "total loaded cases count:" . count ( $this->getPlugin ()->storeCaseManager->storeItemCases ) );
		// var_dump($this->pgin->storeItemCases);
	}


    /**
     * @param Player $sender
     * @param        $path
     * @return null|string
     */
	public static function listsCases(Player $sender, $path) {
		$xpath = $path . self::ITEMCASE_DIR;
		if (! file_exists ( $xpath )) {
			@mkdir ( $xpath, 0777, true );
			return null;
		}
		$output = "Item Cases:\n";
		$handler = opendir ( $xpath );
		$i = 1;
		while ( ($filename = readdir ( $handler )) !== false ) {
			if ($filename != "." && $filename != "..") {
				$data = new Config ( $xpath . $filename, Config::YAML );
				$name = str_replace ( ".yml", "", $filename );
				// $this->log ($filename);
				$itemId = $data->get ( "itemId" );
				$levelname = $data->get ( "levelName" );
				$pos = new Position ( $data->get ( "positionX" ), $data->get ( "positionY" ), $data->get ( "positionZ" ) );
				$output .= $i . ". " . $name . " | (" . $itemId . ") at " . $pos->x . " " . $pos->y . " " . $pos->z . "\n";
				$i ++;
			}
		}
		// $sender->sendMessage ( $output );
		closedir ( $handler );
		return $output;
	}

    /**
     * @param Player $sender
     * @param        $statueName
     * @return StatueModel|null
     */
	public function findStatueByName(Player $sender, $statueName) {
		$path = $this->getPlugin ()->getDataFolder () . self::STATUE_DIR;
		if (! file_exists ( $path )) {
			@mkdir ( $this->getPlugin ()->getDataFolder (), 0777, true );
			@mkdir ( $path );
		}
		$handler = opendir ( $path );
		while ( ($filename = readdir ( $handler )) !== false ) {
			if ($filename != "." && $filename != "..") {
				$data = new Config ( $path . $filename, Config::YAML );
				$name = str_replace ( ".yml", "", $filename );
				$levelname = $data->get ( "levelName" );
				$networkId = $data->get ( "networkId" );
				$type = $data->get ( "type" );
				$pos = new Position ( $data->get ( "positionX" ), $data->get ( "positionY" ), $data->get ( "positionZ" ) );
				if ($name == $statueName) {
					$this->log ( " found statue " . $name . " | " . $pos );
					return new StatueModel ( $name, $type, $networkId, $pos, $levelname );
				}
			}
		}
		closedir ( $handler );
		return null;
	}
	
	/**
	 *
	 * @param PlayerInteractEvent $event        	
	 * @return boolean
	 */
	public function handleTapOnItemCase(PlayerInteractEvent $event) {
		$success = true;
		$b = $event->getBlock ();
		if ($event->getPlayer () instanceof Player) {
			$player = $event->getPlayer ();
			foreach ( $this->storeItemCases as $itemcase ) {
				if ($itemcase instanceof ItemCaseModel) {
					if (! empty ( $itemcase->buyPos )) {
						$blockPosKey = round ( $b->x ) . "." . round ( $b->y ) . "." . round ( $b->z );
						$casePosKey = round ( $itemcase->buyPos->x ) . "." . round ( $itemcase->buyPos->y ) . "." . round ( $itemcase->buyPos->z );
						if ($blockPosKey === $casePosKey) {
							// check player balances
							$data = $this->getPlugin ()->profileprovider->retrievePlayerByName ( $player->getName () );
							if ($data === null || count ( $data ) == 0) {
								$this->getPlugin ()->profileprovider->addPlayer ( $player->getName () );
							}
							if ($data != null && $data [0] ["vip"] == "true") {
								$this->getGameKit ()->takeItemCase ( $player, $itemcase );
								$player->sendMessage ( $this->getMsg ( "bh.play.itemcase.vipfree" ) );
								$player->setNameTag ( "VIP| " . $player->getName () );
								break;
							} else {
								if ($data != null && $data [0] ["balance"] >= $itemcase->price) {
									$this->getGameKit ()->takeItemCase ( $player, $itemcase );
									$player->sendMessage ( $this->getMsg ( "bh.play.itemcase.thanks" ) . $itemcase->name . $this->getMsg ( "bh.play.itemcase.purchased" ) . $itemcase->price . $this->getMsg ( "bh.play.itemcase.coins" ) . "\n" );
									$this->getPlugin ()->profileprovider->withdraw ( $player->getName (), $itemcase->price );
								} elseif ($data != null && $data [0] ["balance"] < $itemcase->price) {
									$player->sendMessage ( $this->getMsg ( "bh.play.itemcase.notenoughtcoins" ) . $itemcase->price );
								}
								break;
							}
						}
					}
				}
			}
		}
		return $success;
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