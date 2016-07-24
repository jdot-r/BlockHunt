<?php

namespace mcg76\game\blockhunt\defence;

use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;

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
 * Defence Model
 *        
 */
class DefenceModel {	
	const DEFENCE_DIR = 'defence/';
	const DEFENCE_LOBBY = "gamecenter";
	public $name;
	public $levelName;
	public $p1;
	public $p2;
	public $entrance;
	public $type;
	public $effect;
	// public $authorizedUsers = [];
	public function __construct($name) {
		$this->name = $name;
	}
	public function save($pluginpath) {
		$path = $pluginpath . self::DEFENCE_DIR;
		if (! file_exists ( $path )) {
			@mkdir ( $path, 0777, true );
		}
		$name = $this->name;
		$data = new Config ( $path . "$name.yml", Config::YAML );
		// this should not happen
		if ($this->levelName == null) {
			$this->levelName = "world";
			$this->pgin->getLogger ()->info ( "Level Name not exist " . $this->$levelName );
		}
		$data->set ( "name", $this->name );
		$data->set ( "levelName", $this->levelName );
		if ($this->p1 != null) {
			$data->set ( "point1X", $this->p1->x );
			$data->set ( "point1Y", $this->p1->y );
			$data->set ( "point1Z", $this->p1->z );
		}
		if ($this->p2 != null) {
			$data->set ( "point2X", $this->p2->x );
			$data->set ( "point2Y", $this->p2->y );
			$data->set ( "point2Z", $this->p2->z );
		}
		$data->set ( "type", $this->type );
		$data->set ( "effect", $this->effect );
		if ($this->entrance != null) {
			$data->set ( "entraceX", $this->entrance->x );
			$data->set ( "entraceY", $this->entrance->y );
			$data->set ( "entraceZ", $this->entrance->z );
		}
		$data->save ();
	}
	public static function getData($path, $name) {
		// $name = $this->accountName;
		if (! file_exists ( $path . self::DEFENCE_DIR . "$name.yml" )) {
			return null;
		}
		$data = new Config ( $path . self::DEFENCE_DIR . "$name.yml", Config::YAML );
		$data->getAll ();
		return $data;
	}
	public static function load($sender, $path, $name) {
		// $name = $this->accountName;
		if (! file_exists ( $path . self::DEFENCE_DIR . "$name.yml" )) {
			return null;
		}
		$data = new Config ( $path . self::DEFENCE_DIR . "$name.yml", Config::YAML );
		$data->getAll ();
		if ($data != null) {
			$newplot = new DefenceModel( $data->get ( "name" ) );
			$newplot->name = $data->get ( "name" );
			$newplot->levelName = $data->get ( "levelName" );
			$newplot->type = $data->get ( "type" );
			$newplot->effect = $data->get ( "effect" );
			$x = $data->get ( "point1X" );
			$y = $data->get ( "point1Y" );
			$z = $data->get ( "point1Z" );
			if ($x != null && $y != NULL && $z != NULL) {
				$newplot->p1 = new Position ( $x, $y, $z );
			}
			$x = $data->get ( "point2X" );
			$y = $data->get ( "point2Y" );
			$z = $data->get ( "point2Z" );
			if ($x != null && $y != NULL && $z != NULL) {
				$newplot->p2 = new Position ( $x, $y, $z );
			}
			$x = $data->get ( "entraceX" );
			$y = $data->get ( "entraceY" );
			$z = $data->get ( "entraceZ" );
			if ($x != null && $y != NULL && $z != NULL) {
				$newplot->entrance = new Position ( $x, $y, $z );
			}
			return $newplot;
		}
		return null;
	}
	public function delete($path) {
		$xpath = $path . self::DEFENCE_DIR;
		$name = $this->name;
		@unlink ( $path . "$name.yml" );
		
		$this->unlinkRecursive ( $path . "$name.yml", false );
	}
	public function isInRectable($centerX, $centerY, $radius, $x, $y) {
		return $x >= $centerX - $radius && $x <= $centerX + $radius && $y >= $centerY - $radius && $y <= $centerY + $radius;
	}
	public function isPointInCircle($centerX, $centerY, $radius, $x, $y) {
		if ($this->isInRectable ( $centerX, $centerY, $radius, $x, $y )) {
			$dx = $centerX - $x;
			$dy = $centerY - $y;
			$dx *= $dx;
			$dy *= $dy;
			$distanceSquared = $dx + $dy;
			$radiusSquared = $radius * $radius;
			return $distanceSquared <= $radiusSquared;
		}
		return false;
	}
	public function inside2(Position $p) {
		$bx = $this->between ( round ( $this->p1->x ), round ( $p->x ), round ( $this->p2->x ) );
		$by = $this->between ( round ( $this->p1->y ), round ( $p->y ), round ( $this->p2->y ) );
		$bz = $this->between ( round ( $this->p1->z ), round ( $p->z ), round ( $this->p2->z ) );
		if ($bx == 1 && $by == 1 && $bz == 1) {
			return 1;
		}
		return 0;
	}
	public function contains($pos) {
		if ((min ( $this->p1->getX (), $this->p2->getX () ) <= $pos->getX ()) && (max ( $this->p1->getX (), $this->p2->getX () ) >= $pos->getX ()) && (min ( $this->p1->getY (), $this->p2->getY () ) <= $pos->getY ()) && (max ( $this->p1->getY (), $this->p2->getY () ) >= $pos->getY ()) && (min ( $this->p1->getZ (), $this->p2->getZ () ) <= $pos->getZ ()) && (max ( $this->p1->getZ (), $this->p2->getZ () ) >= $pos->getZ ())) {
			return true;
		} else {
			return false;
		}
	}
	public function inside($ppos) {
		if ((min ( $this->p1->getX (), $this->p2->getX () ) <= $ppos->getX ()) && (max ( $this->p1->getX (), $this->p2->getX () ) >= $ppos->getX ()) && (min ( $this->p1->getY (), $this->p2->getY () ) <= $ppos->getY ()) && (max ( $this->p1->getY (), $this->p2->getY () ) >= $ppos->getY ()) && (min ( $this->p1->getZ (), $this->p2->getZ () ) <= $ppos->getZ ()) && (max ( $this->p1->getZ (), $this->p2->getZ () ) >= $ppos->getZ ())) {
			return true;
		} else {
			return false;
		}
	}
	
	public function between($l, $m, $r) {
		$lm = abs ( $l - $m );
		$rm = abs ( $r - $m );
		$lrm = $lm + $rm;
		$lr = abs ( $l - $r );
		if ($lrm <= $lr) {
			return 1;
		}
		return 0;
	}
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
}