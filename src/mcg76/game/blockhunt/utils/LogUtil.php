<?php
namespace mcg76\game\blockhunt\utils;

use pocketmine\utils\Config;
use pocketmine\plugin\Plugin;

/**
 * MCPE BlockHunt Minigame - Made by minecraftgenius76
 *
 * You're allowed to use for own usage only "as-is". 
 * you're not allowed to republish or resell or for any commercial purpose.
 *
 * Thanks for your cooperate!
 *
 * Copyright (C) 2014 minecraftgenius76
 * 
 * Web site: http://www.minecraftgenius76.com/
 * YouTube : http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76
 *
 */
/**
 * MCG76 LogUtil
 */
class LogUtil {

	public static function printLog(Plugin $pg, \Exception $e){
		$errout="";
		$errout="message:".$e->getMessage()."\n";
		$errout="file:".$e->getFile()."\n";
		$errout="code:".$e->getCode()."\n";
		$errout="line:".$e->getLine()."\n";
		$errout="trace:".$e->getTraceAsString()."\n";
		$pg->getLogger ()->error ($errout);
	}
	
	public static function logInfo(Plugin $pg, $message){
		$pg->getLogger()->info($message);
	}
}