<?php

namespace mcg76\game\blockhunt\arenas;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\block\Block;

class ArenaBlock {
	public $id;
	public $name;
	public $damage = 0;
	
	public $x;
	public $y;
	public $z;
	public $level;
	public $levelName;

	public function __construct($id, $damage, $x, $y, $z) {
		$this->id = $id;
		$this->damage = $damage;
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
	}	
	
	public function getPosition() {
		return new Position($this->x,$this->y,$this->z, $this->level);
	}
}