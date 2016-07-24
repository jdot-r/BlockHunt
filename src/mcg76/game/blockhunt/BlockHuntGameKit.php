<?php

namespace mcg76\game\blockhunt;

use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\itemcase\ItemCaseModel;

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
 * MCG76 BlockHuntGameKit
 */
class BlockHuntGameKit {
	const DIR_KITS = "kits/";
	// kit types
	const KIT_DIAMOND_ARMOR = "vip_kit";
	const KIT_GOLD_ARMOR = "gold_kit";
	const KIT_IRON_ARMOR = "iron_kit";
	const KIT_LEATHER_ARMOR = "leather_kit";
	const KIT_CHAIN_ARMOR = "chain_kit";
	const KIT_FREE_NO_ARMOR = "free_kit";
	const KIT_SEEKER = "seeker_kit";
	const KIT_HIDER = "hider_kit";
	const KIT_UNKNOWN = "Unknown";
	private $kits = [ ];
	protected $plugin;
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
		$this->init ();
	}
	public function getPlugin() {
		return $this->plugin;
	}
	private function init() {
		@mkdir ( $this->getPlugin ()->getDataFolder () . self::DIR_KITS, 0777, true );
		self::getKit ( $this->getPlugin ()->getDataFolder (), self::KIT_SEEKER );
		self::getKit ( $this->getPlugin ()->getDataFolder (), self::KIT_HIDER );
		$this->kits = array (
				self::getKit ( $this->getPlugin ()->getDataFolder (), self::KIT_SEEKER ),
				self::getKit ( $this->getPlugin ()->getDataFolder (), self::KIT_HIDER ) 
		);
	}
	
	/**
	 * generate a random kits for player
	 *
	 * @param Player $p        	
	 */
	public function putOnRandomGameKit(Player $p) {
		$kittype = array_rand ( $this->kits );
		$selectedKit = $this->kits [$kittype];
		$this->putOnGameKit ( $p, $selectedKit );
	}
	
	/**
	 * wear game kits
	 *
	 * @param Player $p        	
	 * @param unknown $kitType        	
	 */
	public function putOnGameKit(Player $p, $kitType) {
		switch ($kitType) {
			
			case self::KIT_SEEKER :
				$this->loadKit ( self::KIT_SEEKER, $p );
				break;
			case self::KIT_HIDER :
				$this->loadKit ( self::KIT_HIDER, $p );
				break;
			default :
				$this->loadKit ( self::KIT_UNKNOWN, $p );
		}
	}
	
	/**
	 * Get Game Kit By Name
	 *
	 * @param unknown $kitName        	
	 * @return \pocketmine\utils\Config
	 */
	public static function getKit($pluginDataFolder, $kitName) {
		if (! (file_exists ( $pluginDataFolder . self::DIR_KITS . strtolower ( $kitName ) . ".yml" ))) {
			@mkdir ( $pluginDataFolder . self::DIR_KITS, 0777, true );
			if ($kitName == self::KIT_GOLD_ARMOR) {
				return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( self::KIT_GOLD_ARMOR ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_GOLD_ARMOR,
						"isDefault" => false,
						"price" => 80,
						"quantity" => 1,
						"health" => 20,
						"itemOnHand" => 283,
						"armors" => array (
								"helmet" => array (
										Item::GOLD_HELMET,
										"0",
										"1" 
								),
								"chestplate" => array (
										Item::GOLD_CHESTPLATE,
										"0",
										"1" 
								),
								"leggings" => array (
										Item::GOLD_LEGGINGS,
										"0",
										"1" 
								),
								"boots" => array (
										Item::GOLD_BOOTS,
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								Item::GOLD_SWORD => array (
										Item::GOLD_SWORD,
										"0",
										"1" 
								),
								item::SNOWBALL => array (
										item::SNOWBALL,
										"0",
										"64" 
								) 
						),
						"foods" => array (
								Item::APPLE => array (
										Item::APPLE,
										"0",
										"2" 
								),
								Item::CARROT => array (
										Item::CARROT,
										"0",
										"2" 
								) 
						) 
				) );
			} elseif ($kitName == self::KIT_IRON_ARMOR) {
				return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_IRON_ARMOR,
						"isDefault" => false,
						"price" => 20,
						"quantity" => 1,
						"health" => 20,
						"itemOnHand" => 267,
						"armors" => array (
								"helmet" => array (
										Item::IRON_HELMET,
										"0",
										"1" 
								),
								"chestplate" => array (
										Item::IRON_CHESTPLATE,
										"0",
										"1" 
								),
								"leggings" => array (
										Item::IRON_LEGGINGS,
										"0",
										"1" 
								),
								"boots" => array (
										Item::IRON_BOOTS,
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								Item::IRON_SWORD => array (
										Item::IRON_SWORD,
										"0",
										"1" 
								),
								item::SNOWBALL => array (
										item::SNOWBALL,
										"0",
										"64" 
								) 
						),
						"foods" => array (
								item::COOKED_BEEF => array (
										item::COOKED_BEEF,
										"0",
										"2" 
								),
								Item::COOKED_CHICKEN => array (
										Item::COOKED_CHICKEN,
										"0",
										"2" 
								) 
						) 
				) );
			} 

			elseif ($kitName == self::KIT_HIDER) {
				return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( self::KIT_HIDER ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_HIDER,
						"isDefault" => false,
						"price" => 20,
						"quantity" => 1,
						"health" => 20,
						"itemOnHand" => Item::WOODEN_SWORD,
						"armors" => array (
								"helmet" => array (
										Item::AIR,
										"0",
										"1" 
								),
								"chestplate" => array (
										Item::AIR,
										"0",
										"1" 
								),
								"leggings" => array (
										Item::AIR,
										"0",
										"1" 
								),
								"boots" => array (
										Item::AIR,
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								Item::WOODEN_SWORD => array (
										Item::WOODEN_SWORD,
										"0",
										"1" 
								) 
						) 
				) );
			} 

			elseif ($kitName == self::KIT_SEEKER) {
				return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( self::KIT_SEEKER ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_SEEKER,
						"isDefault" => false,
						"price" => 20,
						"quantity" => 1,
						"health" => 20,
						"itemOnHand" => Item::STONE_SWORD,
						"armors" => array (
								"helmet" => array (
										Item::IRON_HELMET,
										"0",
										"1" 
								),
								"chestplate" => array (
										Item::IRON_CHESTPLATE,
										"0",
										"1" 
								),
								"leggings" => array (
										Item::IRON_LEGGINGS,
										"0",
										"1" 
								),
								"boots" => array (
										Item::IRON_BOOTS,
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								Item::IRON_SWORD => array (
										Item::IRON_SWORD,
										"0",
										"1" 
								) 
						) 
				) );
			} 

			elseif ($kitName == self::KIT_CHAIN_ARMOR) {
				return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_CHAIN_ARMOR,
						"isDefault" => false,
						"price" => 50,
						"quantity" => 1,
						"health" => 20,
						"itemOnHand" => 267,
						"armors" => array (
								"helmet" => array (
										Item::CHAIN_HELMET,
										"0",
										"1" 
								),
								"chestplate" => array (
										Item::CHAIN_CHESTPLATE,
										"0",
										"1" 
								),
								"leggings" => array (
										Item::CHAIN_LEGGINGS,
										"0",
										"1" 
								),
								"boots" => array (
										Item::CHAIN_BOOTS,
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								Item::IRON_SWORD => array (
										Item::IRON_SWORD,
										"0",
										"1" 
								),
								item::SNOWBALL => array (
										item::SNOWBALL,
										"0",
										"64" 
								) 
						),
						"foods" => array (
								item::COOKED_PORKCHOP => array (
										item::COOKED_PORKCHOP,
										"0",
										"2" 
								),
								Item::COOKED_CHICKEN => array (
										Item::COOKED_CHICKEN,
										"0",
										"2" 
								) 
						) 
				) );
			} elseif ($kitName == self::KIT_DIAMOND_ARMOR) {
				return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_DIAMOND_ARMOR,
						"isDefault" => false,
						"price" => 100,
						"quantity" => 1,
						"health" => 20,
						"itemOnHand" => 276,
						"armors" => array (
								"helmet" => array (
										Item::DIAMOND_HELMET,
										"0",
										"1" 
								),
								"chestplate" => array (
										Item::DIAMOND_CHESTPLATE,
										"0",
										"1" 
								),
								"leggings" => array (
										Item::DIAMOND_LEGGINGS,
										"0",
										"1" 
								),
								"boots" => array (
										Item::DIAMOND_BOOTS,
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								Item::DIAMOND_SWORD => array (
										Item::DIAMOND_SWORD,
										"0",
										"1" 
								),
								item::SNOWBALL => array (
										item::SNOWBALL,
										"0",
										"64" 
								) 
						),
						"foods" => array (
								item::APPLE => array (
										item::APPLE,
										"0",
										"2" 
								),
								Item::CAKE => array (
										Item::CAKE,
										"0",
										"2" 
								) 
						) 
				) );
			} elseif ($kitName == self::KIT_LEATHER_ARMOR) {
				return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_CHAIN_ARMOR,
						"isDefault" => false,
						"price" => 20,
						"quantity" => 1,
						"health" => 20,
						"itemOnHand" => 276,
						"armors" => array (
								"helmet" => array (
										Item::LEATHER_CAP,
										"0",
										"1" 
								),
								"chestplate" => array (
										Item::LEATHER_TUNIC,
										"0",
										"1" 
								),
								"leggings" => array (
										Item::LEATHER_PANTS,
										"0",
										"1" 
								),
								"boots" => array (
										Item::LEATHER_BOOTS,
										"0",
										"1" 
								) 
						),
						"weapons" => array (
								Item::STONE_SWORD => array (
										Item::STONE_SWORD,
										"0",
										"1" 
								),
								item::SNOWBALL => array (
										item::SNOWBALL,
										"0",
										"64" 
								) 
						),
						"foods" => array (
								item::COOKED_PORKCHOP => array (
										item::COOKED_PORKCHOP,
										"0",
										"2" 
								),
								Item::COOKED_CHICKEN => array (
										Item::COOKED_CHICKEN,
										"0",
										"2" 
								) 
						) 
				) );
			} elseif ($kitName == self::KIT_FREE_NO_ARMOR) {
				return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array (
						"kitName" => self::KIT_FREE_NO_ARMOR,
						"isDefault" => false,
						"price" => 0,
						"quantity" => 1,
						"health" => 20,
						"itemOnHand" => 268,
						"armors" => array (
								"helmet" => array (
										Item::AIR,
										"0",
										"0" 
								),
								"chestplate" => array (
										Item::AIR,
										"0",
										"0" 
								),
								"leggings" => array (
										Item::AIR,
										"0",
										"0" 
								),
								"boots" => array (
										Item::AIR,
										"0",
										"0" 
								) 
						),
						"weapons" => array (
								Item::STONE_SWORD => array (
										Item::STONE_SWORD,
										"0",
										"1" 
								),
								item::SNOWBALL => array (
										item::SNOWBALL,
										"0",
										"64" 
								) 
						),
						"foods" => array (
								item::COOKED_PORKCHOP => array (
										item::COOKED_PORKCHOP,
										"0",
										"2" 
								),
								Item::COOKED_CHICKEN => array (
										Item::COOKED_CHICKEN,
										"0",
										"2" 
								) 
						) 
				) );
			}
		} else {
			return new Config ( $pluginDataFolder . self::DIR_KITS . strtolower ( $kitName ) . ".yml", Config::YAML, array () );
		}
		return;
	}
	
	/**
	 * Load Game Kit
	 *
	 * @param unknown $teamkitName        	
	 * @param Player $p        	
	 */
	public function loadKit($teamkitName, Player $p) {
		$teamKit = $this->getKit ( $this->getPlugin ()->getDataFolder (), $teamkitName )->getAll ();
		if (! empty ( $p ) and ! empty ( $p->getInventory () )) {
			$p->getInventory ()->clearAll ();
			// add armors
			if ($teamKit ["armors"] ["helmet"] [0] != null) {
				$p->getInventory ()->setHelmet ( new Item ( $teamKit ["armors"] ["helmet"] [0], $teamKit ["armors"] ["helmet"] [1], $teamKit ["armors"] ["helmet"] [2] ) );
			}
			if ($teamKit ["armors"] ["chestplate"] [0] != null) {
				$p->getInventory ()->setChestplate ( new Item ( $teamKit ["armors"] ["chestplate"] [0], $teamKit ["armors"] ["chestplate"] [1], $teamKit ["armors"] ["chestplate"] [2] ) );
			}
			if ($teamKit ["armors"] ["leggings"] [0] != null) {
				$p->getInventory ()->setLeggings ( new Item ( $teamKit ["armors"] ["leggings"] [0], $teamKit ["armors"] ["leggings"] [1], $teamKit ["armors"] ["leggings"] [2] ) );
			}
			if ($teamKit ["armors"] ["boots"] [0] != null) {
				$p->getInventory ()->setBoots ( new Item ( $teamKit ["armors"] ["boots"] [0], $teamKit ["armors"] ["boots"] [1], $teamKit ["armors"] ["boots"] [2] ) );
			}
			$p->getInventory ()->sendArmorContents ( $p );
			// set health
			$p->setHealth ( 20 );
			$weapons = $teamKit ["weapons"];
			foreach ( $weapons as $w ) {
				$item = new Item ( $w [0], $w [1], $w [2] );
				$p->getInventory ()->addItem ( $item );
			}
			// $p->getInventory ()->setHeldItemIndex ( 0 );
			$p->getInventory ()->sendArmorContents ( $p->getInventory ()->getViewers () );
			$p->getInventory ()->sendContents ( $p );
			$p->getInventory ()->sendContents ( $p->getViewers () );
		}
	}
	public function takeItemCase(Player $p, ItemCaseModel $itemcase) {
		$item = new Item ( $itemcase->itemId, 0, $itemcase->itemQuantity );
		$p->getInventory ()->addItem ( $item );
		$p->getInventory ()->setItemInHand ( $item );
	}
	
	/**
	 *
	 * @param BlockHuntPlugIn $plugin        	
	 * @param Player $bp        	
	 */
	public static function removePlayerAllInventories(Player $bp) {
		
		if ($bp != null && ! empty ( $bp->getInventory () )) {
			$bp->getInventory ()->setBoots ( new Item ( Item::AIR, 0, 1 ) );
			$bp->getInventory ()->setChestplate ( new Item ( Item::AIR, 0, 1 ) );
			$bp->getInventory ()->setHelmet ( new Item ( Item::AIR, 0, 1 ) );
			$bp->getInventory ()->setLeggings ( new Item ( Item::AIR, 0, 1 ) );
			$bp->getInventory ()->clearAll ();
			$bp->getInventory ()->sendContents ( $bp );
			$bp->getInventory ()->sendContents ( $bp->getViewers () );
		}
	}
	
	/**
	 * 
	 * @param BlockHuntPlugIn $plugin
	 * @param Player $bp
	 */
	public static function backupAndRemovePlayerInventories(BlockHuntPlugIn $plugin, Player $bp) {
		// save a serialized copy
		if (! empty ( $bp->getInventory () )) {
			$pitems = $bp->getInventory ()->getContents ();
			if (count ( $pitems ) > 0) {
				$s = serialize ( $pitems );
				$path = $plugin->getDataFolder ();
				if (! file_exists ( $path )) {
					@mkdir ( $path, 0755, true );
				}
				file_put_contents ( $plugin->getDataFolder () . $bp->getName () . ".items", $s );
			}
		}
		
		if ($bp != null && ! empty ( $bp->getInventory () )) {
			$bp->getInventory ()->setBoots ( new Item ( Item::AIR, 0, 1 ) );
			$bp->getInventory ()->setChestplate ( new Item ( Item::AIR, 0, 1 ) );
			$bp->getInventory ()->setHelmet ( new Item ( Item::AIR, 0, 1 ) );
			$bp->getInventory ()->setLeggings ( new Item ( Item::AIR, 0, 1 ) );
			$bp->getInventory ()->clearAll ();
			$bp->getInventory ()->sendContents ( $bp );
			$bp->getInventory ()->sendContents ( $bp->getViewers () );
		}
	}
	
	/**
	 * 
	 * @param BlockHuntPlugIn $plugin
	 * @param Player $bp
	 */
	public static function restoreBackupPlayerInventories(BlockHuntPlugIn $plugin, Player $bp) {
		if (! empty ( $bp->getInventory () )) {
			$output = "";
			$s = file_get_contents ( $plugin->getDataFolder () . $bp->getName () . ".items" );
			if (empty ( $s )) {
				$player->sendMessage ( TextFormat::YELLOW . "[BH] player inventory backup copy not found!" );
				return;
			}
			$items = unserialize ( $s );
			if (count ( $items ) > 0) {
				$bp->getInventory ()->setContents ( $items );
			}
		}
	}
	
	/**
	 * Load Game Kit
	 *
	 * @param string $teamkitName        	
	 * @param Player $p        	
	 * @return StatueModel
	 */
	public static function createStatueWithKit($pluginDataFolder, $kitName) {
		$teamKit = PvPKit::getKitContent ( $pluginDataFolder . self::DIR_KITS, $kitName )->getAll ();
		// armors
		$name = $kitName;
		$itemOnHand = null;
		$armorHelmet = null;
		$armorChestplate = null;
		$armorLegging = null;
		$armorBoots = null;
		$quantity = 1;
		$price = 0;
		
		if ($teamKit ["armors"] ["helmet"] [0] != null) {
			$armorHelmet = $teamKit ["armors"] ["helmet"] [0];
		}
		if ($teamKit ["armors"] ["chestplate"] [0] != null) {
			$armorChestplate = $teamKit ["armors"] ["chestplate"] [0];
		}
		if ($teamKit ["armors"] ["leggings"] [0] != null) {
			$armorLegging = $teamKit ["armors"] ["leggings"] [0];
		}
		if ($teamKit ["armors"] ["boots"] [0] != null) {
			$armorBoots = $teamKit ["armors"] ["boots"] [0];
		}
		// item on hand
		if ($teamKit ["itemOnHand"] != null) {
			$itemOnHand = $teamKit ["itemOnHand"];
		}
		if ($teamKit ["itemOnHand"] != null) {
			$quantity = $teamKit ["itemOnHand"];
		}
		if ($teamKit ["itemOnHand"] != null) {
			$price = $teamKit ["itemOnHand"];
		}
		
		$model = new StatueModel ( $name, StatueModel::STATUE_TYPE_NPC, 1, null, null, $itemOnHand, $armorHelmet, $armorChestplate, $armorLegging, $armorBoots );
		$model->quantity = $quantity;
		$model->price = $price;
		
		return $model;
	}
	public static function getRandomItems($level, $block) {
		if ($level == null || $block == null) {
			throw new \InvalidStateException ( "level or block may not be null" );
		}
		$tile = $level->getTile ( new Vector3 ( $block->x, $block->y, $block->z ) );
		if ($tile != null) {
			$inv = $tile->getRealInventory ();
			$inv->setItem ( 5, self::randomItems () );
		}
	}
	public static function randomItems() {
		$i = rand ( 0, 50 );
		if ($i == 0) {
			return new Item ( Item::BONE, 0, 1 );
		}
		if ($i == 20) {
			return new Item ( Item::BAKED_POTATO, 0, 1 );
		}
		if ($i == 1) {
			return new Item ( Item::CARROT, 0, 12 );
		}
		if ($i == 21) {
			return new Item ( Item::BREAD, 0, 5 );
		}
		if ($i == 2) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 3) {
			return new Item ( Item::BREAD, 0, 1 );
		}
		if ($i == 4) {
			return new Item ( Item::CAKE, 0, 1 );
		}
		
		if ($i == 5) {
			return new Item ( Item::DIAMOND, 0, 1 );
		}
		if ($i == 6) {
			return new Item ( Item::GOLD_ORE, 0, 1 );
		}
		if ($i == 7) {
			return new Item ( Item::EMERALD_ORE, 0, 2 );
		}
		if ($i == 8) {
			return new Item ( Item::EMERALD_ORE, 0, 1 );
		}
		if ($i == 9) {
			return new Item ( Item::WHEAT, 0, 2 );
		}
		if ($i == 10) {
			return new Item ( Item::SEEDS, 0, 1 );
		}
		if ($i == 11) {
			return new Item ( Item::STRING, 0, 1 );
		}
		if ($i == 12) {
			return new Item ( Item::SUGAR, 0, 1 );
		}
		if ($i == 13) {
			return new Item ( Item::SUGARCANE, 0, 2 );
		}
		if ($i == 14) {
			return new Item ( Item::IRON_HELMET, 0, 1 );
		}
		if ($i == 17) {
			return new Item ( Item::IRON_PICKAXE, 0, 1 );
		}
		if ($i == 18) {
			return new Item ( Item::IRON_SHOVEL, 0, 1 );
		}
		if ($i == 19) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 20) {
			return new Item ( Item::CARROT, 0, 1 );
		}
		if ($i == 21) {
			return new Item ( Item::CAKE, 0, 1 );
		}
		if ($i == 23) {
			return new Item ( Item::COOKED_BEEF, 0, 1 );
		}
		if ($i == 24) {
			return new Item ( Item::COOKED_CHICKEN, 0, 1 );
		}
		if ($i == 25) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 26) {
			return new Item ( Item::COOKED_PORKCHOP, 0, 1 );
		}
		if ($i == 27) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 28) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 29) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 30) {
			return new Item ( Item::APPLE, 0, 1 );
		}
		if ($i == 33) {
			return new Item ( Item::IRON_AXE, 0, 1 );
		}
		if ($i == 34) {
			return new Item ( Item::IRON_SWORD, 0, 1 );
		}
		if ($i == 35) {
			return new Item ( Item::IRON_HOE, 0, 1 );
		}
		if ($i == 36) {
			return new Item ( Item::WOODEN_PICKAXE, 0, 1 );
		}
		if ($i == 37) {
			return new Item ( Item::WOODEN_SWORD, 0, 1 );
		}
		if ($i == 38) {
			return new Item ( Item::WOODEN_HOE, 0, 1 );
		}
		
		return new Item ( Item::AIR );
	}
}