<?php

namespace mcg76\game\blockhunt\itemcase;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\item\Item;

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
class ItemCaseModel {
	const ITEMCASE_DIR = 'itemcase/';
	const ITEMCASE_TYPE_ITEM = 'itemcase_item';
	const ITEMCASE_TYPE_MOD = 'itemcase_mod';
	public $eid;
	public $name;
	// public $networkId;
	public $position;
	public $levelName;
	// item
	public $itemId;
	public $itemName;
	public $itemQuantity = 1;
	public $itemStandBlockId = Item::BED_BLOCK;
	public $itemCoverBlockId = Item::GLASS;
	// cost
	public $price;
	public $discount;
	public $coupon;
	public $points;
	// click link - info
	public $linkPos;
	// click link - buy
	public $buyPos;
	
	
	public function __construct($eid, $name, $itemId, $itemQuantity = 1, $price = 0) {
		$this->name = $name;
		$this->itemId = $itemId;
		$this->eid = $eid;
		$this->itemQuantity = $itemQuantity;
		$this->price = $price;
	}
	public function __toString() {
		return $this->$name . " (" . $this->eid . ")\n" . $this->position . " (" . $this->levelName . ")\n";
	}
	public function save($path) {
		$xpath = $path . self::ITEMCASE_DIR;
		if (! file_exists ( $xpath )) {
			@mkdir ( $xpath, 0777, true );
		}
		$name = $this->name;
		$data = new Config ( $path . self::ITEMCASE_DIR . "$name.yml", Config::YAML );
		$data->set ( "name", $name );
		$data->set ( "eid", $this->eid );
		$data->set ( "itemId", $this->itemId );
		$data->set ( "itemName", $this->itemName );
		$data->set ( "levelName", $this->levelName );
		if ($this->position != null && $this->position != false) {
			$data->set ( "positionX", round ( $this->position->x ) );
			$data->set ( "positionY", round ( $this->position->y ) );
			$data->set ( "positionZ", round ( $this->position->z ) );
		}
		if ($this->linkPos != null && $this->linkPos != false) {
			$data->set ( "linkPosX", round ( $this->linkPos->x ) );
			$data->set ( "linkPosY", round ( $this->linkPos->y ) );
			$data->set ( "linkPosZ", round ( $this->linkPos->z ) );
		}
		if ($this->buyPos != null && $this->buyPos != false) {
			$data->set ( "buyPosX", round ( $this->buyPos->x ) );
			$data->set ( "buyPosY", round ( $this->buyPos->y ) );
			$data->set ( "buyPosZ", round ( $this->buyPos->z ) );
		}
		$data->set ( "itemStandBlockId", $this->itemStandBlockId );
		$data->set ( "itemCoverBlockId", $this->itemCoverBlockId );
		$data->set ( "price", $this->price );
		$data->set ( "discount", $this->discount );
		$data->set ( "coupon", $this->coupon );
		$data->set ( "points", $this->points );
		$data->set ( "quantity", $this->itemQuantity );		
		$data->save ();
	}
	public function load($path) {
        $name = $this->name;
		if (! file_exists ( $path . self::ITEMCASE_DIR . "$name.yml" )) {
			return null;
		}
		$data = new Config ( $path . self::ITEMCASE_DIR . "$name.yml", Config::YAML );
		$data->getAll ();
		if ($data != null) {
			$this->name = $data->get ( "name" );
			$this->eid = $data->get ( "eid" );
			$this->itemId = $data->get ( "itemId" );
			$this->itemName = $data->get ( "itemName" );
			$this->levelName = $data->get ( "levelName" );
			$px = $data->get ( "positionX" );
			$py = $data->get ( "positionY" );
			$pz = $data->get ( "positionZ" );
			$this->position = new Position ( $px, $py, $pz );
			
			$kx = $data->get ( "linkPosX" );
			$ky = $data->get ( "linkPosY" );
			$kz = $data->get ( "linkPosZ" );
			$this->linkPos = new Position ( $kx, $ky, $kz );
			
			$bx = $data->get ( "buyPosX" );
			$by = $data->get ( "buyPosY" );
			$bz = $data->get ( "buyPosZ" );
			$this->buyPos = new Position ( $bx, $by, $bz );
			
			$this->itemStandBlockId = $data->get ( "itemStandBlockId" );
			$this->itemCoverBlockId = $data->get ( "itemCoverBlockId" );
			$this->price = $data->get ( "price" );
			$this->discount = $data->get ( "discount" );
			$this->coupon = $data->get ( "coupon" );
			$this->points = $data->get ( "points" );
			$this->itemQuantity  = $data->get ( "quantity");
		}
		return $this;
	}
	public function getData($path) {
        $name = $this->name;
		if (! file_exists ( $path . self::ITEMCASE_DIR . "$name.yml" )) {
			return null;
		}
		$data = new Config ( $path . self::ITEMCASE_DIR . "$name.yml", Config::YAML );
		$data->getAll ();
		return $data;
	}
	public function toString() {
		$output = "";
		$output .= "name : " . $this->name . "\n";
		$output .= "type : " . $this->type . "\n";
		$output .= "eid : " . $this->eid . "\n";
		$output .= "itemId : " . $this->itemId . "\n";
		if ($this->position != null && $this->position != false) {
			$output .= "position : " . $this->position->x . " " . $this->position->y . " " . $this->position->z . "\n";
		}		
		if ($this->linkPos != null && $this->linkPos != false) {
			$output .= "linkPos : " . $this->linkPos->x . " " . $this->linkPos->y . " " . $this->linkPos->z . "\n";
		}		
		if ($this->buyPos != null && $this->buyPos != false) {
			$output .= "buyPos : " . $this->buyPos->x . " " . $this->buyPos->y . " " . $this->buyPos->z . "\n";
		}		
		$output .= "itemStandBlockId : " . $this->itemStandBlockId . "\n";
		$output .= "itemCoverBlockId : " . $this->itemCoverBlockId . "\n";
		$output .= "price : " . $this->price . "\n";
		$output .= "discount : " . $this->discount . "\n";
		$output .= "coupon : " . $this->coupon . "\n";
		$output .= "points : " . $this->points . "\n";
		
		return $output;
	}
	public function delete($path) {
		$name = $this->name;
		@unlink ( $path . ItemCaseModel::ITEMCASE_DIR . "$name.yml" );
	}
}