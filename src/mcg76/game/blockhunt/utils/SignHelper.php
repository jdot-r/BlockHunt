<?php
namespace mcg76\game\blockhunt\utils;

use pocketmine\utils\Config;
use pocketmine\plugin\Plugin;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\level\Position;
use mcg76\game\blockhunt\arenas\ArenaModel;
use pocketmine\utils\TextFormat;
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
 * 
 * Web site: http://www.minecraftgenius76.com/
 * YouTube : http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76
 *
 */
/**
 * MCG76 SignHelper
 */
class SignHelper {
	
	final static public function updateHallOfFrameWinners(BlockHuntPlugIn $plugin) {

		// update podium
		$topWinners = $plugin->profileprovider->retrieveTopPlayers ();
		$level = $plugin-> homeLevel;
	
		$winners = [ ];
		if (count ( $topWinners ) === 1) {
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getDiamondSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! empty ( $sign )) {
				$sign->setText ( TextFormat::WHITE."[TOP RANKS]", TextFormat::AQUA."#1",TextFormat::WHITE.$goldplayer, TextFormat::GRAY."wins:" . TextFormat::WHITE.$topWinners [0] ["wins"] );
			}
		}
	
		if (count ( $topWinners ) === 2) {
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getDiamondSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! empty ( $sign )) {
				$sign->setText ( TextFormat::WHITE."[TOP RANKS]", TextFormat::AQUA."#1",TextFormat::WHITE.$goldplayer, TextFormat::GRAY."wins:" . TextFormat::WHITE.$topWinners [0] ["wins"] );
			}
	
			$silverplayer = $topWinners [1] ["pname"];
			$signPos1 = $plugin->setup->getGoldSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! empty ( $sign )) {
				$sign->setText ( TextFormat::WHITE."[TOP RANKS]",TextFormat::AQUA."#2",TextFormat::WHITE.$silverplayer, TextFormat::GRAY."wins:" . TextFormat::WHITE.$topWinners [1] ["wins"] );
			}
		}
	
		if (count ( $topWinners ) === 3) {
	
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getDiamondSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! empty ( $sign )) {
				$sign->setText ( TextFormat::WHITE."[TOP RANKS]", TextFormat::AQUA."#1",TextFormat::WHITE.$goldplayer, TextFormat::GRAY."wins:" . TextFormat::WHITE.$topWinners [0] ["wins"] );
			}
	
			$silverplayer = $topWinners [1] ["pname"];
			$signPos2 = $plugin->setup->getGoldSignPos();
			$sign = $level->getTile ( $signPos2 );
			if (! empty ( $sign )) {
				$sign->setText ( TextFormat::WHITE."[TOP RANKS]",TextFormat::AQUA."#2",TextFormat::WHITE.$silverplayer, TextFormat::GRAY."wins:" . TextFormat::WHITE.$topWinners [1] ["wins"] );
			}
	
			$brownseplayer = $topWinners [2] ["pname"];
			$signPos3 = $plugin->setup->getSilverSignPos();
			$sign = $level->getTile ( $signPos3 );
			if (! empty( $sign )) {
				$sign->setText ( TextFormat::WHITE."[TOP RANKS]",TextFormat::AQUA."#3", TextFormat::WHITE.$brownseplayer, TextFormat::GRAY."wins: " . TextFormat::WHITE.$topWinners [2] ["wins"] );
			}
		}
	
	}
	
	final static public function updateHallOfFrameWinnersSeeker(BlockHuntPlugIn $plugin) {
		// update podium
		$topWinners = $plugin->profileprovider->retrieveTopSeekers();
		$level = $plugin->homeLevel;
		//var_export($topWinners);
	
		$winners = [ ];
		if (count ( $topWinners ) === 1) {
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getSeekerGoldSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Seeker #1]", TextFormat::WHITE.$goldplayer, TextFormat::GRAY."wins:" . TextFormat::YELLOW.$topWinners [0] ["win_seeker"], TextFormat::GRAY."conglatulation" );
			}
		}
	
		if (count ( $topWinners ) === 2) {
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getSeekerGoldSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText (TextFormat::DARK_GREEN. "[Best Seeker #1]", TextFormat::WHITE.$goldplayer, TextFormat::GRAY."wins:" . TextFormat::YELLOW.$topWinners [0] ["win_seeker"], TextFormat::GRAY."conglatulation" );
			}
	
			$silverplayer = $topWinners [1] ["pname"];
			$signPos1 = $plugin->setup->getSeekerSilverSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Seeker #1]", TextFormat::WHITE.$silverplayer, TextFormat::GRAY."wins:" .TextFormat::YELLOW. $topWinners [1] ["win_seeker"], TextFormat::GRAY."conglatulation" );
			}
		}
	
		if (count ( $topWinners ) === 3) {
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getSeekerGoldSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Seeker #1]", TextFormat::WHITE.$goldplayer, TextFormat::GRAY."wins: " . TextFormat::YELLOW.$topWinners [0] ["win_seeker"], TextFormat::GRAY."conglatulation" );
			}
	
			$silverplayer = $topWinners [1] ["pname"];
			$signPos1 = $plugin->setup->getSeekerSilverSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Seeker #2]",TextFormat::WHITE.$silverplayer, TextFormat::GRAY."wins: " . TextFormat::YELLOW.$topWinners [1] ["win_seeker"], TextFormat::GRAY."conglatulation" );
			}
				
			$brownseplayer = $topWinners [2] ["pname"];
			$signPos1 = $plugin->setup->getSeekerBronseSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Seeker #3]", TextFormat::WHITE.$brownseplayer, TextFormat::GRAY."wins: " . TextFormat::YELLOW.$topWinners [2] ["win_seeker"], TextFormat::GRAY."conglatulation" );
			}
		}
	}
	
	final static public function updateHallOfFrameWinnersHider(BlockHuntPlugIn $plugin) {
		// update podium
		$topWinners =$plugin->profileprovider->retrieveTopHiders();
		$level = $plugin->homeLevel;		
		//var_export($topWinners);
	
		$winners = [ ];
		if (count ( $topWinners ) === 1) {
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getHiderGoldSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Hider #1]", TextFormat::WHITE.$goldplayer, "wins:" . TextFormat::YELLOW.$topWinners [0] ["win_hider"], TextFormat::GRAY."conglatulation" );
			}
		}
	
		if (count ( $topWinners ) === 2) {
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getHiderGoldSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Hider #1]", TextFormat::WHITE.$goldplayer, "wins:" . TextFormat::YELLOW.$topWinners [0] ["win_hider"], TextFormat::GRAY."conglatulation" );
			}
	
			$silverplayer = $topWinners [1] ["pname"];
			$signPos1 = $plugin->setup->getHiderSilverSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Hider #2]", TextFormat::WHITE.$silverplayer, "wins:" . TextFormat::YELLOW.$topWinners [1] ["win_hider"], TextFormat::GRAY."conglatulation" );
			}
		}
	
		if (count ( $topWinners ) === 3) {
			$goldplayer = $topWinners [0] ["pname"];
			$signPos1 = $plugin->setup->getHiderGoldSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Hider #1]", TextFormat::WHITE.$goldplayer, "wins: " . TextFormat::YELLOW.$topWinners [0] ["win_hider"], TextFormat::GRAY."conglatulation" );
			}
	
			$silverplayer = $topWinners [1] ["pname"];
			$signPos1 = $plugin->setup->getHiderSilverSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Hider #2]",  TextFormat::WHITE.$silverplayer, "wins: " . TextFormat::YELLOW.$topWinners [1] ["win_hider"], TextFormat::GRAY."conglatulation" );
			}
	
			$brownseplayer = $topWinners [2] ["pname"];
			$signPos1 = $plugin->setup->getHiderBronseSignPos();
			$sign = $level->getTile ( $signPos1 );
			if (! is_null ( $sign )) {
				$sign->setText ( TextFormat::DARK_GREEN."[Best Hider #3]",  TextFormat::WHITE.$brownseplayer, "wins: " . TextFormat::YELLOW.$topWinners [2] ["win_hider"], TextFormat::GRAY."conglatulation" );
			}
		}
	}

}