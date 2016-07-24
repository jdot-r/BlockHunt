<?php

namespace mcg76\game\blockhunt;

use pocketmine\utils\Config;

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
class BlockHuntMessages extends MiniGameBase {
	private $messages;
	public function __construct(BlockHuntPlugIn $plugin) {
		parent::__construct ( $plugin );
		$this->loadLanguageMessages ();
	}
	public function getMessageByKey($key) {
		return isset ( $this->messages [$key] ) ? $this->messages [$key] : $key;
	}
	public function getMessageWithVars($node, $vars) {
		$msg = $this->messages->getNested ( $node );
		
		if ($msg != null) {
			$number = 0;
			foreach ( $vars as $v ) {
				$msg = str_replace ( "%var$number%", $v, $msg );
				$number ++;
			}
			return $msg;
		}
		return null;
	}
	public function getVersion() {
		return $this->messages->get ( "version" );
	}
	private function parseMessages(array $messages) {
		$result = [ ];
		foreach ( $messages as $key => $value ) {
			if (is_array ( $value )) {
				foreach ( $this->parseMessages ( $value ) as $k => $v ) {
					$result [$key . "." . $k] = $v;
				}
			} else {
				$result [$key] = $value;
			}
		}
		return $result;
	}
	
	/**
	 * Load Languages
	 */
	public function loadLanguageMessages() {
		try {
			if (! file_exists ( $this->getPlugin ()->getDataFolder () )) {
				@mkdir ( $this->getPlugin ()->getDataFolder (), 0777, true );
				file_put_contents ( $this->getPlugin ()->getDataFolder () . "config.yml", $this->getPlugin ()->getResource ( "config.yml" ) );
			}
			$this->getPlugin ()->saveDefaultConfig ();
			// retrieve language setting
			$configlang = $this->getSetup ()->getMessageLanguage ();
			$messageFile = $this->getPlugin ()->getDataFolder () . "messages_" . $configlang . ".yml";
			$this->getPlugin ()->getLogger ()->info ( "BlockHunt Message Language = " . $messageFile );
			if (! file_exists ( $messageFile )) {
				$this->getPlugin ()->getLogger ()->info ( "Default to EN" );
				file_put_contents ( $this->getPlugin ()->getDataFolder () . "messages_EN.yml", $this->getPlugin ()->getResource ( "messages_EN.yml" ) );
				$msgConfig = new Config ( $this->getPlugin ()->getDataFolder () . "messages_EN.yml" );
				$messages = $msgConfig->getAll ();
				$this->messages = $this->parseMessages ( $messages );
			} else {
				$this->getPlugin ()->getLogger ()->info ( "use existing" );
				$messages = (new Config ( $messageFile ))->getAll ();
				$this->messages = $this->parseMessages ( $messages );
			}
		} catch ( \Exception $e ) {
			$this->plugin->getLogger ()->info ( $e->getMessage () );
		}
	}
	public function reloadMessages() {
		$this->messages->reload ();
	}
	public static function prefixMsg(&$msg) {
		return "[BH]" . $msg;
	}
	
	public function runTests() {
		$this->testMessage("test.name");
	}
	
	public function testMessage($key) {
		$value = $this->getMsgKey($key);
		if ($value==null) {
			$value = TextFormat::RED ."* KEY NOT FOUND !!!";
		}
		if ($key==$value) {
			$value = TextFormat::RED ."* KEY NOT FOUND !!!";
		}
		$this->getPlugin()->getLogger()->info($key." = ".$value);
	}
	
	public function getMsgKey($key) {
		return $this->msgs->getMessageByKey($key);
	}
	
}