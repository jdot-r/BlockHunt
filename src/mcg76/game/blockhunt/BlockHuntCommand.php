<?php

namespace mcg76\game\blockhunt;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\utils\LevelUtil;

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
class BlockHuntCommand extends MiniGameBase {
	/**
	 *
	 * @param BlockHuntPlugIn $plugin        	
	 */
	public function __construct(BlockHuntPlugIn $plugin) {
		parent::__construct ( $plugin );
	}
	
	/**
	 * onCommand
	 *
	 * @param CommandSender $sender        	
	 * @param Command $command        	
	 * @param unknown $label        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		// check command names
		if (((strtolower ( $command->getName () ) === "blockhunt" || strtolower ( $command->getName () ) === "bh")) && isset ( $args [0] )) {
			try {
				$output = "";
				if (! $sender instanceof Player) {
					$output .= $this->getMsg ( "bh.command.in-game-only" );
					$sender->sendMessage ( $output );
					return;
				}
				
				if (strtolower ( $args [0] ) === "list") {
					$sender->sendMessage ( "[BH] Active Arenas:" );
					foreach ( $this->getPlugin ()->getArenaManager ()->playArenas as $arena ) {
						$sender->sendMessage ( $arena->name );
					}
				}
				
				if (strtolower ( $args [0] ) === "backup") {
					if (count ( $args ) != 2) {
						$output = "[BH] missing parameters [/bh unlock arena]";
						$sender->sendMessage ( $output );
						return;
					}
					$arenaName = $args [1];
					LevelUtil::backupPlotBlocks ( $this->plugin, $sender, $arenaName );
				}
				
				if (strtolower ( $args [0] ) === "restore") {
					if (count ( $args ) != 2) {
						$output = "[BH] missing parameters [/bh restore arena]";
						$sender->sendMessage ( $output );
						return;
					}
					$arenaName = $args [1];
					LevelUtil::restorePlotBlocks ( $this->plugin, $sender, $arenaName );
				}
				
				if (strtolower ( $args [0] ) === "activate") {
					if (count ( $args ) != 2) {
						$output .= "Missing parameter [arena name].\n";
						$sender->sendMessage ( $output );
						return;
					}
					$arenaName = $args [1];
					$arena = &$this->plugin->getArenaManager ()->playArenas [$arenaName];
					if ($arena === null) {
						$output .= "arena name [" . $arenaName . "] not found\n";
						$sender->sendMessage ( $output );
						return;
					}
					if ($arena instanceof ArenaModel) {
						$arena->activateArena ( $this->plugin->getDataFolder () . ArenaModel::ARENA_DIR );
						$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
						$output .= "Activeated Arena [" . $args [1] . "]\n";
						$sender->sendMessage ( $output );
						// reload
						$this->getPlugin ()->arenaManager->loadArenas ();
					}
					return;
				}
				
				if (strtolower ( $args [0] ) === "deactivate") {
					if (count ( $args ) != 2) {
						$output .= "Missing parameter [arena name].\n";
						$sender->sendMessage ( $output );
						return;
					}
					$arenaName = $args [1];
					$arena = &$this->plugin->getArenaManager ()->playArenas [$arenaName];
					if ($arena === null) {
						$output .= "arena name [" . $arenaName . "] not found\n";
						$sender->sendMessage ( $output );
						return;
					}
					if ($arena instanceof ArenaModel) {
						$arena->deactiveArena ( $this->plugin->getDataFolder () . ArenaModel::ARENA_DIR );
						$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
						$output .= "Deactiveated Arena [" . $args [1] . "]\n";
						$sender->sendMessage ( $output );
						// reload
						$this->getPlugin ()->arenaManager->loadArenas ();
					}
					return;
				}
				
				if (strtolower ( $args [0] ) === "disablereset") {
					if (count ( $args ) != 2) {
						$output .= "Missing parameter [arena name].\n";
						$sender->sendMessage ( $output );
						return;
					}
					$arenaName = $args [1];
					$arena = &$this->plugin->getArenaManager ()->playArenas [$arenaName];
					if ($arena === null) {
						$output .= "arena name [" . $arenaName . "] not found\n";
						$sender->sendMessage ( $output );
						return;
					}
					if ($arena instanceof ArenaModel) {
						$arena->resetNewGame = false;
						$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
						$output .= TextFormat::YELLOW . "Disabled Arena auto reset [" . $args [1] . "]\n";
						$sender->sendMessage ( $output );
						$arena->save ( $this->plugin->getDataFolder () );
						$this->getPlugin ()->arenaManager->loadArenas ();
					}
					return;
				}
				
				if (strtolower ( $args [0] ) === "enablereset") {
					if (count ( $args ) != 2) {
						$output .= "Missing parameter [arena name].\n";
						$sender->sendMessage ( $output );
						return;
					}
					$arenaName = $args [1];
					$arena = &$this->plugin->getArenaManager ()->playArenas [$arenaName];
					if ($arena === null) {
						$output .= "arena name [" . $arenaName . "] not found\n";
						$sender->sendMessage ( $output );
						return;
					}
					if ($arena instanceof ArenaModel) {
						$arena->resetNewGame = true;
						$this->getPlugin ()->getArenaManager ()->playArenas [$arenaName] = $arena;
						$output .= TextFormat::GREEN . "Enabled Arena auto reset [" . $args [1] . "]\n";
						$sender->sendMessage ( $output );
						$arena->save ( $this->plugin->getDataFolder () );
						$this->getPlugin ()->arenaManager->loadArenas ();
					}
					return;
				}
				
				if (strtolower ( $args [0] ) === "newarena") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->createArena ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "arenawand") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleWandCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "seekerdoorwand") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleWandSeekerDoorCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "defencewand") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->defenceManager->handleWandCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "newdefence") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->defenceManager->newDefence ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "defences") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->defenceManager->loadDefences ( $sender->getPlayer () );
					return;
				}
				
				if (strtolower ( $args [0] ) === "addblock") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleAddBlockCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "clear") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleDeSelCommand ( $sender );
					return;
				}
				
				if (strtolower ( $args [0] ) === "setlobby") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleSetLobbyCommand ( $sender, $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "setserverlobby") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleSetServerLobbyCommand ( $sender, $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "setentrance") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleSetArenaEntranceCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "setexit") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleSetArenaExitCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "setseeker") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleSetArenaSeekerWarpCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "sethider") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleSetArenaHiderWarpCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "setwall") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$this->getPlugin ()->arenaManager->handleSetWallsCommand ( $sender->getPlayer (), $args );
					return;
				}
				
				if (strtolower ( $args [0] ) === "blockon") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					$this->getPlugin ()->pos_display_flag = 1;
					$sender->sendMessage ( $this->getMsg ( "bh.command.blockon.success" ) );
					return;
				}
				
				if (strtolower ( $args [0] ) === "blockoff") {
					if (! $sender->isOp ()) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.no-authorization" ) );
						return;
					}
					$this->getPlugin ()->pos_display_flag = 0;
					$sender->sendMessage ( $this->getMsg ( "bh.command.blockoff.success" ) );
					return;
				}
				
// 				if (strtolower ( $args [0] ) === "givemoney") {
// 					if (! $sender->isOp ()) {
// 						$output = $this->getMsg ( TextFormat::YELLOW . "[BH] Missing authorization" );
// 						$sender->sendMessage ( $output );
// 						return;
// 					}
// 					if (count ( $args ) != 3) {
// 						$sender->sendMessage ( TextFormat::YELLOW . "usage: /givemoney [player] [amount]" );
// 						return;
// 					}
// 					if (empty ( $sender->getServer ()->getPlayerExact ( $args [1] ) )) {
// 						if (empty ( $sender->getServer ()->getOfflinePlayer ( $args [1] ) )) {
// 							$sender->sendMessage ( TextFormat::YELLOW . "[BH] Player [" . $args [1] . "] doesn't exist on this server!" );
// 							return;
// 						}
// 					}
// 					$rs = $this->getPlugin ()->profileprovider->upsetPlayerWinning ( $args [1], $args [2] );
// 					$sender->sendMessage ( TextFormat::GREEN . "[BH] Success! [" . $args [2] . "] gived to player " . $args [1] );
// 					return;
// 				}
				
				if (strtolower ( $args [0] ) === "setbalance") {
				if (! $sender->isOp ()) {
				$output = $this->getMsg ( "bh.command.no-authorization" );
				$sender->sendMessage ( $output );
				return;
				}
				if (count ( $args ) != 3) {
				$sender->sendMessage ( $this->getMsg ( "bh.command.setbalance.usage" ) );
				return;
				}
				$rs = $this->getPlugin ()->profileprovider->setBalance ( $args [1], $args [2] );
				$sender->sendMessage ( $this->getMsg ( "bh.command.setbalance.success" ) );
				return;
				}
				
				if (strtolower ( $args [0] ) === "addvip") {
				if (! $sender->isOp ()) {
				$output = $this->getMsg ( "bh.command.no-authorization" );
				$sender->sendMessage ( $output );
				return;
				}
				if (count ( $args ) != 2) {
				$sender->sendMessage ( $this->getMsg ( "bh.command.addvip.usage" ) );
				return;
				}
				$rs = $this->getPlugin ()->profileprovider->upsetVIP ( $sender->getName (), "true" );
				$sender->sendMessage ( $this->getMsg ( "bh.command.addvip.success" ) );
				return;
				}
				
				if (strtolower ( $args [0] ) === "delvip") {
				if (! $sender->isOp ()) {
				$output = $this->getMsg ( "bh.command.no-authorization" );
				$sender->sendMessage ( $output );
				return;
				}
				if (count ( $args ) != 2) {
				$sender->sendMessage ( $this->getMsg ( "bh.command.delvip.usage" ) );
				return;
				}
				$rs = $this->getPlugin ()->profileprovider->upsetVIP ( $sender->getName (), "false" );
				$sender->sendMessage ( $this->getMsg ( "bh.command.delvip.success" ) );
				return;
				}
				
				if (strtolower ( $args [0] ) === "createprofile") {
				if (! $sender->isOp ()) {
				$output = $this->getMsg ( "bh.command.no-authorization" );
				$sender->sendMessage ( $output );
				return;
				}
				if (count ( $args ) != 2) {
				$sender->sendMessage ( $this->getMsg ( "bh.command.createprofile.usage" ) );
				return;
				}
				$rs = $this->getPlugin ()->profileprovider->addPlayer ( $args [1] );
				$sender->sendMessage ( $this->getMsg ( "bh.command.createprofile.success" ) );
				return;
				}
				
				if (strtolower ( $args [0] ) === "setvip") {
				if (! $sender->isOp ()) {
				$output = $this->getMsg ( "bh.command.no-authorization" );
				$sender->sendMessage ( $output );
				return;
				}
				if (count ( $args ) != 3) {
				$sender->sendMessage ( $this->getMsg ( "bh.command.setvip.usage" ) );
				return;
				}
				$rs = $this->getPlugin ()->profileprovider->upsetVIP ( $sender->getName (), "false" );
				$sender->sendMessage ( $this->getMsg ( "bh.command.setvip.success" ) );
				return;
				}
				
				if (strtolower ( $args [0] ) === "leave" || strtolower ( $args [0] ) === "exit") {
					$this->getPlugin ()->controller->handlePlayerLeavethePlay ( $sender );
					$sender->teleport ( $this->getPlugin ()->setup->getHomeWorldPos () );
					return;
				}
				
				if (strtolower ( $args [0] ) === "home" || strtolower ( $args [0] ) === "lobby") {
					$this->plugin->controller->handlePlayerLeavethePlay ( $sender );
					$sender->teleport ( $this->getPlugin ()->setup->getHomeWorldPos () );
					return;
				}
				
				if (strtolower ( $args [0] ) === "xyz") {
					if ($sender instanceof Player) {
						$portalLevel = $sender->level->getName ();
						$sender->sendMessage ( $this->getMsg ( "bh.command.xyz.success" ) . $portalLevel . " - [" . round ( $sender->x ) . " " . round ( $sender->y ) . " " . round ( $sender->z ) . "]" );
					}
					return;
				}
				// player commands
				if (strtolower ( $args [0] ) === "stats" || strtolower ( $args [0] ) === "stat" || strtolower ( $args [0] ) === "mystat") {
					if (! ($sender instanceof Player)) {
						$output .= $this->getMsg ( "bh.command.in-game-only" );
						$sender->sendMessage ( $output );
						return;
					}
					$data = $this->getPlugin ()->profileprovider->retrievePlayerStats ( $sender->getName () );
					if (count ( $data ) > 0) {
						$output = TextFormat::BOLD . $this->getMsg ( "bh.command.stats.title" ) . "\n";
						$output .= "-----------------------\n";
						$output .= TextFormat::AQUA . $this->getMsg ( "bh.command.stats.wins" ) . "   : " . TextFormat::GOLD . $data [0] ["wins"] . "\n";
						$output .= TextFormat::GRAY . $this->getMsg ( "bh.command.stats.loss" ) . "  : " . TextFormat::GRAY . $data [0] ["loss"] . "\n";
						$output .= "-----------------------\n";
						$output .= TextFormat::BOLD . "Seeker\n";
						$output .= TextFormat::DARK_AQUA . "   wins: " . $data [0] ["win_seeker"] . "\n";
						$output .= TextFormat::GRAY . "   loss: " . $data [0] ["loss_seeker"] . "\n";
						$output .= TextFormat::BOLD . "Hider\n";
						$output .= TextFormat::DARK_AQUA . "   wins: " . $data [0] ["win_hider"] . "\n";
						$output .= TextFormat::GRAY . "   loss: " . $data [0] ["loss_hider"] . "\n";
						$output .= "-----------------------\n";
						$sender->sendMessage ( $output );
					} else {
						$sender->sendMessage ( $this->getMsg ( "bh.command.stats.notfound" ) );
					}
					return;
				}
				
				if (strtolower ( $args [0] ) === "balance") {
					$data = $this->getPlugin ()->profileprovider->retrievePlayerBalance ( $sender->getName () );
					if ($data == null || count ( $data ) == 0) {
						$sender->sendMessage ( $this->getMsg ( "bh.command.balance.failed" ) );
					} else {
						$sender->sendMessage ( $this->getMsg ( "bh.command.balance.success" ) . $data [0] ["balance"] . " coins" );
					}
					return;
				}
				
				if (strtolower ( $args [0] ) === "profile") {
					$data = $this->getPlugin ()->profileprovider->retrievePlayerByName ( $sender->getName () );
					if ($data == null || count ( $data ) == 0) {
						$output = "[BH] No profile found";
					} else {
						$output = "";
						$output .= $this->getMsg ( "bh.command.profile.title" ) . "\n";
						$output .= $this->getMsg ( "bh.command.profile.balance" ) . $data [0] ["balance"] . "\n";
						$output .= $this->getMsg ( "bh.command.profile.wins" ) . $data [0] ["wins"] . "\n";
						$output .= $this->getMsg ( "bh.command.profile.loss" ) . $data [0] ["loss"] . "\n";
						$output .= $this->getMsg ( "bh.command.profile.vip" ) . $data [0] ["vip"] . "\n";
					}
					$sender->sendMessage ( $output );
					return;
				}
				
				if (strtolower ( $args [0] ) === "help") {
					$output = "\nMCG76 BlockHunt Player Commands\n";
					$output .= "------------------------------------\n";
					$output .= $this->getMsg ( "bh.command.info.profile" );
					$output .= $this->getMsg ( "bh.command.info.stats" );
					$output .= $this->getMsg ( "bh.command.info.balance" );
					$output .= $this->getMsg ( "bh.command.info.leave" );
					$output .= $this->getMsg ( "bh.command.info.xyz" );
					$output .= $this->getMsg ( "bh.command.info.home" );
					$sender->sendMessage ( $output );
				}
				if (strtolower ( $args [0] ) == "adminprofile") {
					$output = "\nMCG76 BlockHunt Admin Profile Commands\n";
					$output .= "------------------------------------\n";
					$output .= $this->getMsg ( "bh.command.info.addvip" );
					$output .= $this->getMsg ( "bh.command.info.delvip" );
					$output .= $this->getMsg ( "bh.command.info.setbalance" );
					$output .= $this->getMsg ( "bh.command.info.createprofile" );
					$sender->sendMessage ( $output );
				}
				
				if (strtolower ( $args [0] ) === "adminsetup") {
					$output = "\nMCG76 BlockHunt Admin Setup Commands\n";
					$output .= "------------------------------------\n";
					$output .= $this->getMsg ( "bh.command.info.newarena" );
					$output .= $this->getMsg ( "bh.command.info.arenawand" );
					$output .= $this->getMsg ( "bh.command.info.seekerdoorwand" );
					$output .= $this->getMsg ( "bh.command.info.defencewand" );
					$output .= $this->getMsg ( "bh.command.info.newdefence" );
					$output .= $this->getMsg ( "bh.command.info.addblock" );
					$output .= $this->getMsg ( "bh.command.info.clear" );
					$output .= $this->getMsg ( "bh.command.info.setlobby" );
					$output .= $this->getMsg ( "bh.command.info.setseeker" );
					$output .= $this->getMsg ( "bh.command.info.sethider" );
					$output .= $this->getMsg ( "bh.command.info.blockon" );
					$output .= $this->getMsg ( "bh.command.info.blockoff" );
					$output .= $this->getMsg ( "bh.command.info.xyz" );
					$sender->sendMessage ( $output );
				}
			} catch ( \Exception $e ) {
				$this->log ( "blockhunt error " . $e->__toString () );
			}
		}
	}
}