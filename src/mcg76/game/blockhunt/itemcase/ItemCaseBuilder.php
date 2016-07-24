<?php

namespace mcg76\game\blockhunt\itemcase;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\ItemBlock;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

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
class ItemCaseBuilder {
	private static $instance = null;
	public function __construct() {
		self::$instance = $this;
	}
	public static function getInstance() {
		return self::$instance;
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param ItemCaseModel $itemcase        	
	 */
	public static function spawnCaseItem(Player $player, ItemCaseModel $itemcase) {
		// set position above
		$pos = $itemcase->position;
		// self::renderBlock($player,$pos, Item::BEDROCK);
		$itm = Item::get ( $itemcase->itemId );
		$itm->setCount ( $itemcase->itemQuantity );
		$pk = new AddItemEntityPacket ();
		$pk->eid = $itemcase->eid;
		$pk->item = $itm;
		$pk->x = $pos->x + 0.5;
		$pk->y = $pos->y + 1;
		$pk->z = $pos->z + 0.25;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->roll = 0;
		$player->dataPacket ( $pk );
		Server::broadcastPacket ( $player->getViewers (), $pk );
		
		$px = $pos->x;
		if ($px < 0) {
			$px = $px - 0.5 - 0.15;
		} else {
			$px = $px + 0.5 + 0.15;
		}
		
		//$this->moveToSend[$entityId] = [$entityId, $x, $y, $z, $yaw, $headYaw === \null ? $yaw : $headYaw, $pitch];
		$pk = new MoveEntityPacket ();
		$pk->entities = [ 
				[ 
						$itemcase->eid,
						$px,
						$pos->y + 1 + 0.25,
						$pos->z + 0.25,
						0,
						0,
						0 
				] 
		];		
		$player->dataPacket ( $pk );
		Server::broadcastPacket ( $player->getViewers (), $pk );		
				
		// cover
		$pos->y = $pos->y + 1;
		if (empty ( $itemcase->itemCoverBlockId )) {
			self::renderBlock ( $player, $pos, $itemcase->itemCoverBlockId );
		} else {
			self::renderBlock ( $player, $pos, Item::GLASS );
		}
		// stand
		$pos->y = $pos->y - 1;
		if (empty ( $itemcase->itemStandBlockId )) {
			self::renderBlock ( $player, $pos, $itemcase->itemStandBlockId );
		} else {
			self::renderBlock ( $player, $pos, Item::NETHER_BRICK_BLOCK );
		}
	}
	
	
	public static function renderBlock(Player $lp, Position $pos, $blocktype) {
		$block = Item::get ( $blocktype );
		$direct = false;
		$update = true;
		$lp->getLevel ()->setBlock ( $pos, $block->getBlock (), $direct, $update );
	}
	public static function despawnCase(Player $lp, ItemCaseModel $itemcase) {
		$pk = new RemoveEntityPacket ();
		$pk->eid = $itemcase->eid;
		$pk->isEncoded = true;
		$lp->dataPacket ( $pk );
		Server::broadcastPacket ( $lp->getViewers (), $pk );
		$pos = $itemcase->position;
		$pos->y = $pos->y + 1;
		self::renderBlock ( $lp, $pos, Item::AIR );
		$pos->y = $pos->y - 1;
		self::renderBlock ( $lp, $pos, Item::AIR );
	}
	/**
	 * 
	 * @param Position $currentPos
	 * @param string $currentDir
	 * @param string $distance
	 * @return Vector3
	 */
	private function adjustPosition(Position $currentPos, $currentDir, $distance) {
		$current_y = $currentPos->y;
		$current_x = $currentPos->x;
		$current_z = $currentPos->z;
		
		switch ($currentDir) {
			case '0' :
				$current_z = $current_z + $distance;
				break;
			case '1' :
				$current_x = $current_x - $distance;
				break;
			case '2' :
				$current_z = $current_z - $distance;
				break;
			case '3' :
				$current_x = $current_x + $distance;
				break;
		}
		
		$newPos = new Vector3 ( $current_x, $current_y, $current_z );
		return $newPos;
	}
}