<?php

namespace mcg76\game\blockhunt;

use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\Player;
use pocketmine\Server;
use mcg76\game\blockhunt\BlockHuntPlugIn;

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
class BlockHuntSetup extends MiniGameBase {
	const SERVER_LOBBY_NAME = 1000;
	const SERVER_LOBBY_WORLD = 1001;
	const SERVER_LOBBY_POSITION = 1002;
	const TNTRUN_HOME_NAME = 2002;
	const TNTRUN_HOME_WORLD = 2041;
	const TNTRUN_HOME_POSITION = 2003;
	const TNTRUN_ARENA_NAME = 2001;
	const TNTRUN_ARENA_POSITION = 2010;
	const TNTRUN_ARENA_ENTRANCE_POSITION = 2020;
	const TNTRUN_ARENA_BUILDING_BOARD_BLOCKS = 2030;
	const TNTRUN_ARENA_BUILDING_BOARD = "ArenaBoardTypes";
	const CLICK_BUTTON_JOIN1_GAME = 3001;
	const CLICK_BUTTON_START_GAME = 3010;
	const CLICK_BUTTON_STOP_GAME = 3020;
	const CLICK_BUTTON_RESET_GAME = 3030;
	const CLICK_BUTTON_TOP_FLOOR_EXIT = 3040;
	const CLICK_BUTTON_BOTTOM_FLOOR_EXIT = 3050;
	const CLICK_SIGN_VIEW_GAME_STATS = 4000;
	const CLICK_SIGN_JOIN1_GAME = 4001;
	const CLICK_SIGN_JOIN2_GAME = 4011;
	const CLICK_SIGN_GO_HOME = 4002;
	const CLICK_SIGN_START_GAME = 4003;
	const CLICK_SIGN_GO_LOBBY = 4004;
	const CLICK_SIGN_RESET_GAME = 4005;
	public $signDiamondPos;
	public $signGoldPos;
	public $signSilverPos;
	public $signHiderGoldPos;
	public $signHiderSilverPos;
	public $signHiderBronsePos;
	public $signSeekerGoldPos;
	public $signSeekerSilverPos;
	public $signSeekerBronsePos;
	
	/**
	 * Constructor
	 *
	 * @param        	
	 *
	 */
	public function __construct(BlockHuntPlugIn $plugin) {
		parent::__construct ( $plugin );
		$this->init ();
	}
	private function init() {
	}
	public function getMessageLanguage() {
		$configlang = $this->getPlugIn ()->getConfig ()->get ( "language" );
		if ($configlang == null) {
			$configlang = "EN";
		}
		return $configlang;
	}
	public function setHomeLocation(Position $pos) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "blockhunt_home_world", $pos->getLevel ()->getName () );
			$config->set ( "blockhunt_home_x", round ( $pos->x ) );
			$config->set ( "blockhunt_home_Y", round ( $pos->y ) );
			$config->set ( "blockhunt_home_z", round ( $pos->z ) );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	public function setServerLobbyLocation(Position $pos) {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "server_lobby_world", $pos->getLevel ()->getName () );
			$config->set ( "server_lobby_x", round ( $pos->x ) );
			$config->set ( "server_lobby_y", round ( $pos->y ) );
			$config->set ( "server_lobby_z", round ( $pos->z ) );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	public function isSafeSpawnEnable() {
		$value = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$value = $config->get ( "enable_blockhunt_safespawn", false );
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $value;
	}
	public function enablePlayerOnJoinGoToLobby() {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "enable_spaw_lobby", "YES" );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	public function disablePlayerOnJoinGoToLobby() {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "enable_spaw_lobby", "NO" );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	public function enableSelfReset() {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "reset_scheduler", "YES" );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	public function disableSelfReset() {
		$success = false;
		try {
			$config = $this->getPlugIn ()->getConfig ();
			$config->set ( "reset_scheduler", "NO" );
			$config->save ();
			$success = true;
		} catch ( \Exception $e ) {
			$this->getPlugIn ()->getLogger ()->error ( $e->getMessage () );
		}
		return $success;
	}
	public function getRoundResetTime() {
		$resetValue = $this->getConfig ( "reset_timeout" );
		if ($resetValue == null) {
			$resetValue = 10000;
		}
		return $resetValue;
	}
	public function getRoundResetOptionContinueRunning() {
		$resetoption = $this->getConfig ( "reset_continue_running" );
		if ($resetoption == null) {
			$resetoption = "FULL";
		}
		return $resetoption;
	}
	public function getHomeWorldPos() {
		$dataX = $this->getConfig ( "blockhunt_home_x" );
		$dataY = $this->getConfig ( "blockhunt_home_y" );
		$dataZ = $this->getConfig ( "blockhunt_home_z" );
		if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
			return null;
		} else {
			return new Position ( $dataX, $dataY, $dataZ );
		}
	}
	public function getHomeWorldSignPos() {
		$dataX = $this->getConfig ( "blockhunt_home_sign_x" );
		$dataY = $this->getConfig ( "blockhunt_home_sign_y" );
		$dataZ = $this->getConfig ( "blockhunt_home_sign_z" );
		
		if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
			return null;
		} else {
			return new Position ( $dataX, $dataY, $dataZ );
		}
	}
	public function getDiamondSignPos() {
		if ($this->signDiamondPos === null) {
			$dataX = $this->getConfig ( "bh_podium_diamond_x" );
			$dataY = $this->getConfig ( "bh_podium_diamond_y" );
			$dataZ = $this->getConfig ( "bh_podium_diamond_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signDiamondPos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		return $this->signDiamondPos;
	}
	public function getSilverSignPos() {
		if ($this->signSilverPos === null) {
			$dataX = $this->getConfig ( "bh_podium_silver_x" );
			$dataY = $this->getConfig ( "bh_podium_silver_y" );
			$dataZ = $this->getConfig ( "bh_podium_silver_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signSilverPos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		return $this->signSilverPos;
	}
	
	public function getGoldSignPos() {
		if ($this->signGoldPos === null) {
			$dataX = $this->getConfig ( "bh_podium_gold_x" );
			$dataY = $this->getConfig ( "bh_podium_gold_y" );
			$dataZ = $this->getConfig ( "bh_podium_gold_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signGoldPos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		return $this->signGoldPos;
	}
	
	public function getHiderGoldSignPos() {
		if ($this->signHiderGoldPos === null) {
			$dataX = $this->getConfig ( "bh_hider_podium_gold_x" );
			$dataY = $this->getConfig ( "bh_hider_podium_gold_y" );
			$dataZ = $this->getConfig ( "bh_hider_podium_gold_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signHiderGoldPos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		return $this->signHiderGoldPos;
	}
	public function getHiderSilverSignPos() {
		if ($this->signHiderSilverPos === null) {
			$dataX = $this->getConfig ( "bh_hider_podium_silver_x" );
			$dataY = $this->getConfig ( "bh_hider_podium_silver_y" );
			$dataZ = $this->getConfig ( "bh_hider_podium_silver_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signHiderSilverPos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		return $this->signHiderSilverPos;
	}
	public function getHiderBronseSignPos() {
		if ($this->signHiderBronsePos === null) {
			$dataX = $this->getConfig ( "bh_hider_podium_bronse_x" );
			$dataY = $this->getConfig ( "bh_hider_podium_bronse_y" );
			$dataZ = $this->getConfig ( "bh_hider_podium_bronse_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signHiderBronsePos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		return $this->signHiderBronsePos;
	}
	public function getSeekerGoldSignPos() {
		if ($this->signSeekerGoldPos === null) {
			$dataX = $this->getConfig ( "bh_seeker_podium_gold_x" );
			$dataY = $this->getConfig ( "bh_seeker_podium_gold_y" );
			$dataZ = $this->getConfig ( "bh_seeker_podium_gold_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signSeekerGoldPos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		return $this->signSeekerGoldPos;
	}
	public function getSeekerSilverSignPos() {
		if ($this->signSeekerSilverPos === null) {
			$dataX = $this->getConfig ( "bh_seeker_podium_silver_x" );
			$dataY = $this->getConfig ( "bh_seeker_podium_silver_y" );
			$dataZ = $this->getConfig ( "bh_seeker_podium_silver_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signSeekerSilverPos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		
		return $this->signSeekerSilverPos;
	}
	public function getSeekerBronseSignPos() {
		if ($this->signSeekerBronsePos === null) {
			$dataX = $this->getConfig ( "bh_seeker_podium_bronse_x" );
			$dataY = $this->getConfig ( "bh_seeker_podium_bronse_y" );
			$dataZ = $this->getConfig ( "bh_seeker_podium_bronse_z" );
			if (empty ( $dataX ) || empty ( $dataY ) || empty ( $dataZ )) {
				return null;
			} else {
				$this->signSeekerBronsePos = new Position ( $dataX, $dataY, $dataZ );
			}
		}
		return $this->signSeekerBronsePos;
	}
	public function getHomeWorldName() {
		return $this->getConfig ( "blockhunt_home_world" );
	}
	public function isEnableSpanwToLobby() {
		$enableSpawnLobby = $this->getConfig ( "enable_spaw_lobby" );
		if ($enableSpawnLobby != null && strtolower ( $enableSpawnLobby ) == "yes") {
			return true;
		}
		return false;
	}
	public function getMinimalGameStartPlayers() {
		return $this->getConfig ( "minimal_game_players", 2 );
	}
	public function isEnableScheduledReset() {
		$runSchedule = $this->getConfig ( "reset_scheduler" );
		if ($runSchedule != null && $runSchedule == "ON") {
			return true;
		}
		return false;
	}
	public function getServerLobbyWorldName() {
		return $this->getConfig ( "server_lobby_world" );
	}
	public function getServerLobbyPos() {
		$lobbyX = $this->getConfig ( "server_lobby_x" );
		$lobbyY = $this->getConfig ( "server_lobby_y" );
		$lobbyZ = $this->getConfig ( "server_lobby_z" );
		
		if (empty ( $lobbyX ) || empty ( $lobbyY ) || empty ( $lobbyZ )) {
			return null;
		} else {
			return new Position ( $lobbyX, $lobbyY, $lobbyZ );
		}
	}
	public function getServerLobbySignPos() {
		$lobbyX = $this->getConfig ( "server_lobby_sign_x" );
		$lobbyY = $this->getConfig ( "server_lobby_sign_y" );
		$lobbyZ = $this->getConfig ( "server_lobby_sign_z" );
		
		if (empty ( $lobbyX ) || empty ( $lobbyY ) || empty ( $lobbyZ )) {
			return null;
		} else {
			return new Position ( $lobbyX, $lobbyY, $lobbyZ );
		}
	}
}