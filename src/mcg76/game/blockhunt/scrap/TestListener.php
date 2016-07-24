<?php

namespace mcg76\game\blockhunt\scrap;

use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\Server;
use mcg76\game\blockhunt\itemcase\ItemCaseBuilder;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\entity\Effect;
use pocketmine\event\block\BlockPlaceEvent;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use pocketmine\level\Location;

/**
 * TestListener - Made by minecraftgenius76
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
class TestListener implements Listener {
	public $plugin = null;
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
	}
	public function dataPacketHandler(DataPacketSendEvent $event) {
		$packet = $event->getPacket ();
		if ($packet instanceof MovePlayerPacket) {
			$event->setCancelled ( false );
		}
	}
	
	/**
	 *
	 * @param BlockBreakEvent $event        	
	 */
	public function onBlockBreak(BlockBreakEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if (strtolower ( $event->getPlayer ()->level->getName () ) === strtolower ( $this->getPlugin ()->setup->getHomeWorldName () )) {
				if (! $event->getPlayer ()->isOp ()) {
					$event->setCancelled ( true );
				}
			}
		}
	}
	
	/*
	 * @param PlayerItemHeldEvent $event
	 */
	public function onItemOnHand(PlayerItemHeldEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			$this->getPlugin ()->controller->handleHiderSelectBlockType ( $event->getPlayer (), $event->getItem ()->getId () );
		}
	}
	public function getPlugin() {
		return $this->plugin;
	}
	
	/**
	 *
	 * @param Player $player        	
	 * @param Position $from        	
	 * @param Position $to        	
	 * @param unknown $blockid        	
	 */
	// public function keepBlockMoving(Player $player, Position $from, Position $to, $blockid) {
	// // $player->keepMovement = true;
	// // $effect = Effect::getEffect(Effect::INVISIBILITY);
	// // $effect->setDuration(50);
	// // $player->addEffect($effect);
	// // foreach ( Server::getInstance ()->getOnlinePlayers () as $vp ) {
	// // $player->hidePlayer ( $vp );
	// // }
	
	// // FROM
	// $envbid1 = $player->getLevel()->getBlockIdAt ( round($from->x), round($from->y), round($from->z) );
	// $envbid2 = $player->getLevel()->getBlockIdAt ( round($to->x), round($to->y), round($to->z));
	// $envbid3 = $player->getLevel()->getBlockIdAt ( round($player->x), round($player->y), round($player->z));
	// $envbid4 = $player->getLevel()->getBlockIdAt ( round($player->lastX), round($player->lastY), round($player->lastZ));
	
	// if ($envbid1 === $blockid) {
	// $block = Item::get ( Item::AIR )->getBlock ();
	// $direct = true;
	// $update = false;
	// $player->getLevel()->setBlock ($from, $block, $direct, $update );
	
	// print_r(" From:". $envbid1." | ".round($from->x)." ".round($from->y)." ".round($from->z)."\n");
	// print_r(" To:". $envbid2." | ".round($to->x)." ".round($to->y)." ".round($to->z)."\n");
	// print_r("PCurrent:". $envbid2." | ".round($player->x)." ".round($player->y)." ".round($player->z)."\n");
	// print_r(" PLast:". $envbid2." | ".round($player->lastX)." ".round($player->lastY)." ".round($player->lastZ)."\n");
	
	// if ($envbid2===Item::AIR) {
	// $player->getLevel()->setBlock ( $from , $block, $direct, $update );
	// echo "BUG #1\n";
	// }
	// if ($envbid2===$blockid) {
	// $player->getLevel()->setBlock ( $from, $block, $direct, $update );
	// echo "FIX #1\n";
	// }
	// }
	
	// // CURRENT
	// $envbid = $player->getLevel()->getBlockIdAt ( $player->x, $player->y, $player->z );
	// if ($envbid === Item::AIR) {
	// $block = Item::get ( $blockid )->getBlock ();
	// $direct = true;
	// $update = false;
	// $player->getLevel()->setBlock ( new Position ( round($player->x), round($player->y), round($player->z), $player->getLevel () ), $block, $direct, $update );
	// } elseif ($envbid === $blockid) {
	// $block = Item::get ( $blockid )->getBlock ();
	// $direct = true;
	// $update = false;
	// $player->getLevel()->setBlock ( new Position ( round($player->x), round($player->y), round($player->z), $player->getLevel () ), $block, $direct, $update );
	// }
	
	// // NOT MOVING
	// if (round($player->x) != round($from->x) || round($player->y) != round($from->y) || round($player->z) != round($from->z)) {
	// $envbid = $player->level->getBlockIdAt ( round($to->x), round($to->y), round($to->z));
	// // don't set if it's not a block
	// if ($envbid === Item::AIR) {
	// $block = Item::get ( $blockid )->getBlock ();
	// $direct = true;
	// $update = false;
	// $player->level->setBlock ( new Position ( round($to->x), round($to->y), round($to->z), $player->getLevel () ), $block, $direct, $update );
	// }
	// }
	// }
	/**
	 *
	 * @param Player $player        	
	 * @param unknown $from        	
	 * @param unknown $to        	
	 */
	public function moveHiderBlock(Player $player, $from, $to) {
		// $blockid = $this->getHiderBlock ( $player );
		$blockid = Item::LOG;
		if ($blockid != null) {
			$this->keepBlockMoving ( $player, $from, $to, $blockid );
		}
	}
	
	// TEST SOLUTION
	// public function keepBlockMoving(Player $player, Position $from, Position $to, $blockid) {
	// // $player->keepMovement = true;
	// // $effect = Effect::getEffect(Effect::INVISIBILITY);
	// // $effect->setDuration(50);
	// // $player->addEffect($effect);
	// // foreach ( Server::getInstance ()->getOnlinePlayers () as $vp ) {
	// // $player->hidePlayer ( $vp );
	// // }
	// $envbid1 = $player->getLevel ()->getBlockIdAt ( round ( $from->x ), round ( $from->y ), round ( $from->z ) );
	// $envbid2 = $player->getLevel ()->getBlockIdAt ( round ( $to->x ), round ( $to->y ), round ( $to->z ) );
	// $envbid3 = $player->getLevel ()->getBlockIdAt ( round ( $player->x ), round ( $player->y ), round ( $player->z ) );
	// $envbid4 = $player->getLevel ()->getBlockIdAt ( round ( $player->lastX ), round ( $player->lastY ), round ( $player->lastZ ) );
	
	// // FROM
	// $envbid = $player->getLevel ()->getBlockIdAt ( $from->x, $from->y, $from->z );
	// if ($envbid === $blockid) {
	// $block = Item::get ( Item::AIR )->getBlock ();
	// $direct = true;
	// $update = true;
	// $player->getLevel ()->setBlock ( new Position ( $from->x, $from->y, $from->z, $player->getLevel () ), $block, $direct, $update );
	
	// print_r ( " From: " . $envbid1 . " | " . round ( $from->x ) . " " . round ( $from->y ) . " " . round ( $from->z ) . "\n" );
	// print_r ( " To: " . $envbid2 . " | " . round ( $to->x ) . " " . round ( $to->y ) . " " . round ( $to->z ) . "\n" );
	// print_r ( " PCurrent: " . $envbid3 . " | " . round ( $player->x ) . " " . round ( $player->y ) . " " . round ( $player->z ) . "\n" );
	// print_r ( " PLast: " . $envbid4 . " | " . round ( $player->lastX ) . " " . round ( $player->lastY ) . " " . round ( $player->lastZ ) . "\n" );
	
	// // hider block should not be hidden
	// if ($envbid1 === Item::AIR && $envbid2 === Item::AIR && $envbid3 === Item::AIR && $envbid4 === Item::AIR) {
	// $hblock = Item::get ( $blockid )->getBlock ();
	// $player->getLevel ()->setBlock ( new Position ( $player->lastX, $player->lastY, $player->lastZ, $player->getLevel () ), $hblock, $direct, $update );
	// echo "FIX #1\n";
	// }
	// if ($envbid1 === $envbid2) {
	// $hblock = Item::get ( $blockid )->getBlock ();
	// $player->getLevel ()->setBlock ( new Position ( $player->x, $player->y, $player->z, $player->getLevel () ), $hblock, $direct, $update );
	// echo "FIX #2 - remember block position: " . $envbid2 . "\n";
	// }
	// }
	
	// // CURRENT
	// $envbid = $player->getLevel ()->getBlockIdAt ( $player->x, $player->y, $player->z );
	// if ($envbid === Item::AIR) {
	// $block = Item::get ( $blockid )->getBlock ();
	// $direct = true;
	// $update = true;
	// $player->getLevel ()->setBlock ( new Position ( $player->x, $player->y, $player->z, $player->getLevel () ), $block, $direct, $update );
	// } elseif ($envbid === $blockid) {
	// $block = Item::get ( $blockid )->getBlock ();
	// $direct = true;
	// $update = true;
	// $player->getLevel ()->setBlock ( new Position ( $player->x, $player->y, $player->z, $player->getLevel () ), $block, $direct, $update );
	// }
	// // NOT MOVING
	// if (round ( $player->x ) != round ( $from->x ) || round ( $player->y ) != round ( $from->y ) || round ( $player->z ) != round ( $from->z )) {
	// $envbid = $player->level->getBlockIdAt ( $to->x, $to->y, $to->z );
	// // don't set if it's not a block
	// if ($envbid === Item::AIR) {
	// $block = Item::get ( $blockid )->getBlock ();
	// $direct = true;
	// $update = true;
	// $player->level->setBlock ( $to, $block, $direct, $update );
	// }
	// }
	// }
	
	// // ORIGINAL
	// public function keepBlockMoving(Player $player, Position $from, Position $to, $blockid) {
	// // $player->keepMovement = true;
	// // $effect = Effect::getEffect(Effect::INVISIBILITY);
	// // $effect->setDuration(50);
	// // $player->addEffect($effect);
	// // foreach ( Server::getInstance ()->getOnlinePlayers () as $vp ) {
	// // $player->hidePlayer ( $vp );
	// // }
	// $envbid1 = $player->getLevel ()->getBlockIdAt ( $from->x, $from->y, $from->z );
	// $envbid2 = $player->getLevel ()->getBlockIdAt ( $to->x, $to->y, $to->z );
	// $envbid3 = $player->getLevel ()->getBlockIdAt ( $player->x, $player->y, $player->z );
	// $envbid4 = $player->getLevel ()->getBlockIdAt ( $player->lastX, $player->lastY, $player->lastZ );
	
	// print_r ( " From: " . $envbid1 . " , " . $from->x . ", " . $from->y . ", " . $from->z . "\n" );
	// print_r ( " To: " . $envbid2 . " , " . $to->x . ", " . $to->y . ", " . $to->z . "\n" );
	// print_r ( "PCurrent: " . $envbid3 . " , " . round ( $player->x ) . ", " . $player->y . " " . $player->z . "\n" );
	// print_r ( " PLast: " . $envbid4 . " , " . $player->lastX . ", " . $player->lastY . ", " . $player->lastZ . "\n" );
	// print_r ( " Dplane: " . " , " . $player->getDirectionPlane ()->x . ", " . $player->getDirectionPlane ()->y . "\n" );
	// print_r ( " Dvector: " . " , " . $player->getDirectionVector ()->x . ", " . $player->getDirectionVector ()->y . ", " . $player->getDirectionVector()->z . "\n" );
	// print_r ( " Direct: " . " , " . $player->getDirection () . "\n" );
	
	// // FROM
	// $envbid = $player->getLevel ()->getBlockIdAt ( $from->x, $from->y, $from->z );
	// if ($envbid === $blockid) {
	// $block = Item::get ( Item::AIR )->getBlock ();
	// $direct = true;
	// $update = false;
	
	// // // hider block should not be hidden
	// if ($envbid1 === Item::AIR && $envbid2 === Item::AIR && $envbid3 === Item::AIR && $envbid4 === Item::AIR) {
	// $hblock = Item::get ( $blockid )->getBlock ();
	// $player->getLevel ()->setBlock ( new Position ( $player->lastX, $player->lastY, $player->lastZ, $player->getLevel () ), $hblock, $direct, $update );
	// echo "FIX #1\n";
	// }elseif ($envbid1 === $envbid2 and $envbid3===Item::AIR) {
	// $hblock = Item::get ( $blockid )->getBlock ();
	// $player->getLevel ()->setBlock ( new Position ( $player->x, $player->x, $player->x, $player->getLevel () ), $hblock, $direct, $update );
	// echo "FIX #2 - remember block position: " . $envbid2 . "\n";
	// } else {
	// $player->getLevel ()->setBlock ( new Position ( $from->x, $from->y, $from->z, $player->getLevel () ), $block, $direct, $update );
	// }
	// }
	
	// // CURRENT
	// $envbid = $player->getLevel ()->getBlockIdAt ( $player->x, $player->y, $player->z );
	// if ($envbid === Item::AIR) {
	// $block = Item::get ( $blockid )->getBlock ();
	// $direct = true;
	// $update = false;
	// $player->getLevel ()->setBlock ( new Position ( $player->x, $player->y, $player->z, $player->getLevel () ), $block, $direct, $update );
	// } elseif ($envbid === $blockid) {
	// $block = Item::get ( $blockid )->getBlock ();
	// ;
	// $direct = true;
	// $update = false;
	// $player->getLevel ()->setBlock ( new Position ( $player->x, $player->y, $player->z, $player->getLevel () ), $block, $direct, $update );
	// }
	
	// // NOT MOVING (to and last position is the same then not moving
	// if (round ( $player->x ) != round ( $from->x ) || round ( $player->y ) != round ( $from->y ) || round ( $player->z ) != round ( $from->z )) {
	// $envbid = $player->level->getBlockIdAt ( $to->x, $to->y, $to->z );
	// // don't set if it's not a block
	// if ($envbid === Item::AIR) {
	// $block = Item::get ( $blockid )->getBlock ();
	// $direct = false;
	// $update = false;
	// $player->level->setBlock ( $to, $block, $direct, $update );
	// }
	// }
	// }
	
	/**
	 *
	 * @param PlayerMoveEvent $event        	
	 */
	public function onPlayerMove(PlayerMoveEvent $event) {
		
		/*
		 * 0 = South 1 = West 2 = North 3 = East ### ---------------------- North West: X: -1 Z: -1 North East: X: 0 Z: -1 South West: X: -1 Z: 0 South East: X: 0 Z: 0
		 */
		$player = $event->getPlayer ();
		
		$dx = $player->x;
		$dy = $player->y;
		$dz = $player->z;
		$msg = "\np1[" . $player->getDirection () . "] | " . $dx . " " . $dy . " " . $dz;
		// $this->plugin->log ( $msg );
		
		$player->sendTip ( "[" . $player->getDirection () . "]" );
		$player->sendPopup ( $msg );
		
		$envbid1 = $player->getLevel ()->getBlockIdAt ( round ( $player->x ), round ( $player->y ), round ( $player->z ) );
		$tx = $event->getTo ()->x;
		$tz = $event->getTo ()->z;
		$ty = $event->getTo ()->y;
		
		$fx = $event->getFrom ()->x;
		$fz = $event->getFrom ()->z;
		$fy = $event->getFrom ()->y;
		
		$px = $player->x;
		$pz = $player->z;
		$py = $player->y;
		switch ($player->getDirection ()) {
			// south
			case 0 :
				if ($px < 0) {
					$px = $px - 0.32;
					$pz = $pz - 0.020;
					
					$fx = $event->getFrom ()->x - 0.32;
					$fz = $event->getFrom ()->z - 0.0320;
					
					$tx = $event->getTo ()->x - 0.32;
					$tz = $event->getTo ()->z - 0.032;
				} else {
					$px = $px + 0.32;
					$pz = $pz + 0.020;
					
					$fx = $event->getFrom ()->x + 0.32;
					$fz = $event->getFrom ()->z + 0.0320;
					
					$tx = $event->getTo ()->x + 0.32;
					$tz = $event->getTo ()->z + 0.032;
				}
				
				break;
			// north
			case 2 :
				if ($px < 0) {
					$px = $px - 0.30;
					$pz = $pz - 0.060;
					
					$fx = $event->getFrom ()->x - 0.30;
					$fz = $event->getFrom ()->z - 0.060;
					
					$tx = $event->getTo ()->x - 0.32;
					$tz = $event->getTo ()->z - 0.062;
				} else {
					$px = $px + 0.30;
					$pz = $pz + 0.060;
					
					$fx = $event->getFrom ()->x + 0.30;
					$fz = $event->getFrom ()->z + 0.060;
					
					$tx = $event->getTo ()->x + 0.32;
					$tz = $event->getTo ()->z + 0.06;
				}
				
				break;
			// west
			case 1 :
				if ($px < 0) {
					$px = $px - 0.32;
					$pz = $pz - 0.034;
					
					$fx = $event->getFrom ()->x - 0.32;
					$fz = $event->getFrom ()->z - 0.034;
					
					$tx = $event->getTo ()->x - 0.32;
					$tz = $event->getTo ()->z - 0.032;
				} else {
					$px = $px + 0.32;
					$pz = $pz + 0.034;
					
					$fx = $event->getFrom ()->x + 0.32;
					$fz = $event->getFrom ()->z + 0.034;
					
					$tx = $event->getTo ()->x + 0.32;
					$tz = $event->getTo ()->z + 0.032;
				}
				break;
			// east
			case 3 :
				if ($px < 0) {
					$px = $px - 0.01;
					$pz = $pz - 0.30;
					
					$fx = $event->getFrom ()->x - 0.01;
					$fz = $event->getFrom ()->z - 0.3;
				} else {
					$px = $px + 0.10;
					$pz = $pz + 0.32;
					
					$fx = $event->getFrom ()->x + 0.01;
					$fz = $event->getFrom ()->z + 0.3;
				}
				
				break;
		}
		
		// $player->setPosition ( new Position ( round ( $px ), round ( $py ), round ( $pz ) ) );
		$player->setPosition ( new Position ( $px, round ( $py ), $pz ) );
		
		// $event->setTo (new Location ( $tx, round($ty), $tz));
		$event->setFrom ( new Location ( $fx, round ( $fy ), $fz ) );
		
		$dx = $player->x;
		$dy = $player->y;
		$dz = $player->z;
		$msg = "\np2[" . $player->getDirection () . "] | " . $dx . " " . $dy . " " . $dz;
		// $this->plugin->log ( $msg );
		
		$blockid = Item::LOG;
		$this->keepBlockMoving ( $player, $event->getFrom (), $event->getTo (), $blockid );
		// }
	}
	public function keepBlockMoving(Player $player, Position $from, Position $to, $blockid) {
		// foreach ( $player->getViewers () as $vp ) {
		// if ($vp->getName () != $player->getName ()) {
		// $player->hidePlayer ( $vp );
		// }
		// }
		$dx = $player->getDirectionVector ()->x;
		$dy = $player->getDirectionVector ()->y;
		$dz = $player->getDirectionVector ()->z;
		
		$playerpos = new Position ( $player->x, round ( $player->y ), $player->z, $player->getLevel () );
		$frompos = new Position ( $from->x, round ( $from->y ), $from->z, $player->getLevel () );
		$topos = new Position ( $to->x, round ( $to->y ), $to->z, $player->getLevel () );
		
		$this->plugin->log ( "DIRECTION: " . $player->getDirection () );
		$this->plugin->log ( "dir___," . $dx . "," . $dy . "," . $dz );
		$this->plugin->log ( "from__," . $frompos->x . "," . $frompos->y . "," . $frompos->z );
		$this->plugin->log ( "player," . $playerpos->x . "," . $playerpos->y . "," . $playerpos->z );
		$this->plugin->log ( "to____," . $topos->x . "," . $topos->y . "," . $topos->z );
		
		$envbid = $player->getLevel ()->getBlockIdAt ( $from->x, round ( $from->y ), $from->z );
		if ($envbid === $blockid) {
			$player->getLevel ()->setBlock ( $frompos, $block = Item::get ( Item::AIR )->getBlock (), true, false );
			$player->getLevel ()->setBlock ( $playerpos, $block = Item::get ( Item::AIR )->getBlock (), true, false );
			$player->getLevel ()->setBlock ( new Position ( $player->lastX, round ( $player->lastY ), $player->lastZ ), $block = Item::get ( Item::AIR )->getBlock (), true, false );
		}
		
		$envbid = $player->getLevel ()->getBlockIdAt ( $player->x, round($player->y), $player->z );
		if ($envbid === Item::AIR || $envbid === Item::TALL_GRASS) {
			$player->getLevel ()->setBlock ( $playerpos, Item::get ( $blockid )->getBlock (), true, false );
		}
		// elseif ($envbid === $blockid) {
		// $player->getLevel ()->setBlock ( $player->getPosition (), Item::get ( $blockid )->getBlock (), true, false );
		// }
// 		if (round ( $playerpos->x ) != round ( $frompos->x ) || round ( $playerpos->y ) != round ( $frompos->y ) || round ( $playerpos->z ) != round ( $frompos->z )) {
// 			// $envbid = $player->level->getBlockIdAt ( $topos->x, round($topos->y), $topos->z );
// 			$envbid = $player->level->getBlockIdAt ( $playerpos->x, round ( $playerpos->y ), $playerpos->z );
// 			if ($envbid === Item::AIR || $envbid === Item::TALL_GRASS) {
// 				$player->level->setBlock ( $topos, Item::get ( $blockid )->getBlock (), true, false );
// 			}
// 		}
	}
}