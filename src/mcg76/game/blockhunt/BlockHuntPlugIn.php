<?php

namespace mcg76\game\blockhunt;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use mcg76\game\blockhunt\arenas\ArenaManager;
use mcg76\game\blockhunt\tasks\PlayMonitorTask;
use mcg76\game\blockhunt\itemcase\ItemCaseManager;
use mcg76\game\blockhunt\defence\DefenceManager;
use mcg76\game\blockhunt\itemcase\ItemCaseListener;
use mcg76\game\blockhunt\defence\DefenceListener;
use mcg76\game\blockhunt\arenas\ArenaListener;
use mcg76\game\blockhunt\tasks\PlayStateMachineTask;
use mcg76\game\blockhunt\tasks\PlayArenaGate;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\tasks\PlayArenaRepairTask;
use mcg76\game\blockhunt\scrap\TestListener;

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
class BlockHuntPlugIn extends PluginBase implements CommandExecutor {
	public $commands = null;
	public $arenaManager = null;
	public $gameKit = null;
	public $setup = null;
	public $controller = null;
	public $profileprovider = null;
	public $defenceManager = null;
	public $messages = null;
	public $storeCaseManager = null;
	public $blocks = null;
	public $homeLevel = null;
	public $homeLevelName = null;
	public $pos_display_flag =  false;
	public $setupModeAction = "";
	public $setupModeData = "";
	public $forceReset = true;
	
	public $cacheBlocks=[];
		
	public function getHomeLevel() {
		if (empty($this->homeLevel)) {
			$this->loadHomeLevel();
		}
		return $this->homeLevel;
	}
	public function setHomeLevel($hlevel) {
		$this->homeLevel = $hlevel;
	}
	public function getHomeLevelName() {
		return $this->homeLevelName;
	}
	
	public function getStoreCaseManager() {
		return $this->storeCaseManager;
	}
	
	/**
	 * OnLoad
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onLoad()
	 */
	public function onLoad() {
		$this->registerHelpers();
	}
	
	public function getArenaManager() {
		return $this->arenaManager;
	}
	
	/**
	 * OnEnable
	 *
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {
		$this->initConfigFile ();
		$this->enabled = true;	
		$this->registerListeners();
		$this->checkHelperInitialization();			
		$this->registerScheduleTasks();		
		$this->loadHomeLevel();
		$this->blocks = new BlockHuntBlock();
		$this->log ( TextFormat::GREEN . "- mcg76_BlockHunt - Enabled!" );
	}
	
	private function registerHelpers() {
		$this->commands = new BlockHuntCommand ( $this );
		$this->arenaManager = ArenaManager::getInstance($this);
		$this->defenceManager = DefenceManager::getInstance($this);
		$this->controller = BlockHuntController::getInstance($this );
		$this->profileprovider = new ProfileProvider ( $this );
		$this->gameKit = new BlockHuntGameKit ( $this );
		$this->setup = new BlockHuntSetup ( $this );
		$this->messages = new BlockHuntMessages ( $this );
	}
	
	private function loadHomeLevel() {
		if (empty($this->homeLevel)) {
			$homeWorld = $this->setup->getHomeWorldName ();
			Server::getInstance ()->loadLevel ( $homeWorld );
			$this->setHomeLevel ( Server::getInstance ()->getLevelByName ( $homeWorld ) );
			$this->homeLevelName = $homeWorld;
		}
	}
	
	private function checkHelperInitialization() {
		if ($this->messages == null) {
			$this->getLogger ()->info ( TextFormat::RED . "[BH] 0.Messages not initialized" );
		} else {
			$this->messages->loadLanguageMessages ();
			$this->getLogger ()->info ( TextFormat::RED . "[BH] 0.Messages initialized" );
		}
		// load saved statues files
		if ($this->profileprovider == null) {
			$this->getLogger ()->info ( TextFormat::RED . "[BH] 1.Profile provider not initialized properly" );
		} else {
			$this->profileprovider->initlize ();
			$this->getLogger ()->info ( TextFormat::GREEN . "[BH] 1.Profile provider initialized" );
		}
		if ($this->arenaManager == null) {
			$this->getLogger ()->info ( TextFormat::RED . "[BH] 2.Arena manager not initialized properly" );
		} else {
			$this->arenaManager->loadArenas ();
			$this->getLogger ()->info ( TextFormat::GREEN . "[BH] 2.Arena manager initialized" );
		}
		$this->storeCaseManager = new ItemCaseManager ( $this );
		if ($this->storeCaseManager == null) {
			$this->getLogger ()->info ( TextFormat::RED . "[BH] 3.ItemCase manager not initialized properly" );
		} else {
			$this->storeCaseManager->loadItemCases ();
			$this->getLogger ()->info ( TextFormat::GREEN . "[BH] 3.ItemCase manager initialized" );
		}
		$this->defenceManager = new DefenceManager ( $this );
		if ($this->defenceManager == null) {
			$this->getLogger ()->info ( TextFormat::RED . "[BH] 4.Defence manager not initialized properly" );
		} else {
			$this->defenceManager->loadDefences ();
			$this->getLogger ()->info ( TextFormat::GREEN . "[BH] 4.Defence manager initialized" );
		}
	}
	
	private function registerListeners() {
		$this->getServer ()->getPluginManager ()->registerEvents ( new BlockHuntListener ( $this ), $this );
		$this->getServer ()->getPluginManager ()->registerEvents ( new ItemCaseListener ( $this ), $this );
		$this->getServer ()->getPluginManager ()->registerEvents ( new DefenceListener ( $this ), $this );
		$this->getServer ()->getPluginManager ()->registerEvents ( new ArenaListener ( $this ), $this );
		//$this->getServer ()->getPluginManager ()->registerEvents ( new TestListener( $this ), $this );
	}
	
	private function initConfigFile() {
		try {
			if (! file_exists ( $this->getDataFolder () )) {
				@mkdir ( $this->getDataFolder (), 0777, true );
				file_put_contents ( $this->getDataFolder () . "config.yml", $this->getResource ( "config.yml" ) );
			}
			$this->saveDefaultConfig ();
			$this->reloadConfig ();
			$this->getConfig ()->getAll ();
		} catch ( \Exception $e ) {
			$this->getLogger ()->error ( $e->getMessage () );
		}
	}
	
	private function registerScheduleTasks() {
		$wait_time = 10 * $this->getServer ()->getTicksPerSecond ();
		$arenaResetTask = new PlayStateMachineTask($this );
		$this->getServer ()->getScheduler ()->scheduleRepeatingTask ( $arenaResetTask, 38 );		
	}
	
	/**
	 * OnDisable
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->log ( TextFormat::RED . "mcg76_BlockHunt - Disabled" );
		$this->enabled = false;
	}
	
	/**
	 * OnCommand
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\plugin\PluginBase::onCommand()
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$this->commands->onCommand ( $sender, $command, $label, $args );
	}
	
	// common Logging APIs
	public function log($msg) {
		$this->getLogger ()->info ( $msg );
	}
	public function printError(\Exception $e) {
		$message = "[HG-Error] " . $e->getMessage () . " : " . $e->getCode () . " | line# " . $e->getLine () . "| \n Trace: [" . $e->getTraceAsString () . "]";
		$this->getLogger ()->info($message );
	}
	public function info($msg) {
		$this->getLogger ()->info ( $msg );
	}
	
}
