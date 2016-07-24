<?php

namespace mcg76\game\blockhunt;

use pocketmine\utils\TextFormat;
use mcg76\game\blockhunt\utils\LogUtil;
use mcg76\game\blockhunt\arenas\ArenaManager;


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

/**
 * Profile Provider
 *
 */
class ProfileProvider
{
    const DB_STORE_FILE = "mcg76_BHv2_Profiles.db";
    const DB_SQL_FILE_PROFILE = "sqlite3_player_profile.sql";
    private $plugin;

    public function __construct(BlockHuntPlugIn $pg)
    {
        $this->plugin = $pg;
    }

    public function getPlugIn()
    {
        return $this->plugin;
    }

    private function log($msg)
    {
        $this->plugin->getLogger()->info($msg);
    }

    /**
     * Initialize database
     */
    public function initlize()
    {
        @mkdir($this->getPlugIn()->getDataFolder());
        if (!file_exists($this->getPlugIn()->getDataFolder() . self::DB_STORE_FILE)) {
            $this->getPlugIn()->database = new \SQLite3 ($this->getPlugIn()->getDataFolder() . self::DB_STORE_FILE, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            $resource = $this->getPlugIn()->getResource($this::DB_SQL_FILE_PROFILE);
            $this->getPlugIn()->database->exec(stream_get_contents($resource));
            $this->log(TextFormat::BLUE . "- [BH] Created New Database.");
        } else {
            $this->getPlugIn()->database = new \SQLite3 ($this->getPlugIn()->getDataFolder() . self::DB_STORE_FILE, SQLITE3_OPEN_READWRITE);
            $this->log(TextFormat::BLUE . "- [BH] loaded use existing player database.");
        }
    }


    public function retrieveVIPs()
    {
        $records = [];
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE vip = 'true'");
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
                    $records [] = $data;
                }
                $result->finalize();
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "data error: " . $e->getMessage();
        }
        return $records;
    }

    /**
     * retrieve top 3 players
     *
     * @param unknown $arena
     * @param unknown $owner
     * @return string|multitype:multitype:
     */
    public function retrieveTopPlayers()
    {
        $records = [];
        try {
            $prepare = $this->plugin->database->prepare("SELECT pname, wins from player_profile order by wins desc LIMIT 3");
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
                    $records [] = $data;
                }
                $result->finalize();
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "data error: " . $e->getMessage();
        }
        return $records;
    }
    
    public function retrieveTopHiders()
    {
    	$records = [];
    	try {
    		$sql = "SELECT pname, win_hider from player_profile order by win_hider desc LIMIT 3";
    		$prepare = $this->plugin->database->prepare($sql);
    		$result = $prepare->execute();
    		if ($result instanceof \SQLite3Result) {
    			while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
    				$records [] = $data;
    			}
    			$result->finalize();
    		}
    	} catch (\Exception $e) {
    		LogUtil::printLog($this->getPlugIn(), $e);
    		return "data error: " . $e->getMessage();
    	}
    	return $records;
    }
    
    public function retrieveTopSeekers()
    {
    	$records = [];
    	try {
    		$sql="SELECT pname, win_seeker from player_profile order by win_seeker desc LIMIT 3";
    		$prepare = $this->plugin->database->prepare($sql);
    		$result = $prepare->execute();
    		if ($result instanceof \SQLite3Result) {
    			while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
    				$records [] = $data;
    			}
    			$result->finalize();
    		}
    	} catch (\Exception $e) {
    		LogUtil::printLog($this->getPlugIn(), $e);
    		return "data error: " . $e->getMessage();
    	}
    	return $records;
    }
    
    /**
     * retrieve arena by name
     *
     * @param unknown $arena
     * @param unknown $owner
     * @return string|multitype:multitype:
     */
    public function retrievePlayerByName($pname)
    {
        $records = [];
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
                    $records [] = $data;
                }
                $result->finalize();
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "data error: " . $e->getMessage();
        }
        return $records;
    }

    /**
     * retrieve arena by name
     *
     * @param unknown $arena
     * @param unknown $owner
     * @return string|multitype:multitype:
     */
    public function isPlayerExist($pname)
    {
        $found = false;
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
                    $found = true;
                    break;
                }
                $result->finalize();
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "data error: " . $e->getMessage();
        }
        return $found;
    }

    /**
     * Retrieve Player Stats
     *
     * @param unknown $arena
     * @return string|multitype:multitype:
     */
    public function retrievePlayerStats($pname)
    {
        $records = [];
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname=:pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
                    $records [] = $data;
                }
                $result->finalize();
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "data error: " . $e->getMessage();
        }
        return $records;
    }

    /**
     * Retrieve Player Stats
     *
     * @param unknown $arena
     * @return string|multitype:multitype:
     */
    public function retrievePlayerBalance($pname)
    {
        $records = [];
        try {
            $prepare = $this->plugin->database->prepare("SELECT balance from player_profile WHERE pname=:pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
                    $records [] = $data;
                }
                $result->finalize();
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "data error: " . $e->getMessage();
        }
        return $records;
    }


    /**
     * Retrieve Player Stats
     *
     * @param unknown $arena
     * @return string|multitype:multitype:
     */
    public function retrievePlayerVIP($pname)
    {
        $records = [];
        try {
            $prepare = $this->plugin->database->prepare("SELECT vip from player_profile WHERE pname=:pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
                    $records [] = $data;
                }
                $result->finalize();
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "data error: " . $e->getMessage();
        }
        return $records;
    }

    public function upsetPlayerStats($pname, $balance, $wins, $loss, $role)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    try {
                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET balance=:balance, wins=:wins, loss=:loss, role=:role WHERE pname = :pname");
                        $prepare->bindValue(":balance", $balance, SQLITE3_INTEGER);
                        $prepare->bindValue(":wins", $wins, SQLITE3_INTEGER);
                        $prepare->bindValue(":loss", $loss, SQLITE3_INTEGER);
                        $prepare->bindValue(":role", $loss, SQLITE3_TEXT);
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player profile updated!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    public function addPlayerWinning($pname, $amount)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    try {
                        $newwins = $data ["wins"] + 1;
                        $newbalance = $data ["balance"] + $amount;
                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET balance=:balance, wins=:wins WHERE pname = :pname");
                        $prepare->bindValue(":balance", $newbalance, SQLITE3_INTEGER);
                        $prepare->bindValue(":wins", $newwins, SQLITE3_INTEGER);
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player profile updated!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    public function addPlayerWinningSeeker($pname)
    {
    	try {
    		$prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
    		$prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
    		$result = $prepare->execute();
    		if ($result instanceof \SQLite3Result) {
    			$data = $result->fetchArray(SQLITE3_ASSOC);
    			$result->finalize();
    			if (isset ($data ["pname"])) {
    				try {
    					//$newwins = $data ["wins"] + 1;
    					$winseek = $data ["win_seeker"] + 1;
    					//$newbalance = $data ["balance"] + $amount;
    					$prepare = $this->plugin->database->prepare("UPDATE player_profile SET win_seeker=:win_seeker WHERE pname = :pname");
    					//$prepare->bindValue(":balance", $newbalance, SQLITE3_INTEGER);
    					//$prepare->bindValue(":wins", $newwins, SQLITE3_INTEGER);
    					$prepare->bindValue(":win_seeker", $winseek, SQLITE3_INTEGER);
    					$prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
    					$prepare->execute();
    				} catch (\Exception $e) {
    					LogUtil::printLog($this->getPlugIn(), $e);
    					return "data error: " . $e->getMessage();
    				}
    				return "player profile updated!";
    			}
    		}
    	} catch (\Exception $e) {
    		LogUtil::printLog($this->getPlugIn(), $e);
    		return "db error: " . $e->getMessage();
    	}
    	return null;
    }
    
    public function addPlayerWinningHider($pname)
    {
    	try {
    		$prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
    		$prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
    		$result = $prepare->execute();
    		if ($result instanceof \SQLite3Result) {
    			$data = $result->fetchArray(SQLITE3_ASSOC);
    			$result->finalize();
    			if (isset ($data ["pname"])) {
    				try {
    					$winhinder = $data ["win_hider"] + 1;
    					$prepare = $this->plugin->database->prepare("UPDATE player_profile SET win_hider=:win_hider WHERE pname = :pname");
    					$prepare->bindValue(":win_hider", $winhinder, SQLITE3_INTEGER);
    					$prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
    					$prepare->execute();
    				} catch (\Exception $e) {
    					LogUtil::printLog($this->getPlugIn(), $e);
    					return "data error: " . $e->getMessage();
    				}
    				return "player profile updated!";
    			}
    		}
    	} catch (\Exception $e) {
    		LogUtil::printLog($this->getPlugIn(), $e);
    		return "db error: " . $e->getMessage();
    	}
    	return null;
    }
    
    public function upsetPlayerWinning($pname, $amount)
    {
        try {
            if (!$this->isPlayerExist($pname)) {
                $this->upsetPlayer($pname, $pname, 0, 0, 0, 0, "false", 0, 0, 0, "new",0,0,0,0);
            }
            if ($amount>0) {
	            $rs = $this->addPlayerWinning($pname, $amount);
            }
            return $rs;
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
        }
        return null;
    }

    public function upsetPlayerLoss($pname)
    {
        try {
            if (!$this->isPlayerExist($pname)) {
                $this->upsetPlayer($pname, $pname, 0, 0, 0, 0, "false", 0, 0, 0, "new",0,0,0,0);
            }
            $rs = $this->addPlayerLoss($pname);
            return $rs;
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
        }
        return null;
    }

    public function addVIP($pname)
    {
        $ok = false;
        try {
            if (!$this->isPlayerExist($pname)) {
                $this->upsetPlayer($pname, $pname, 0, 0, 0, 0, "false", 0, 0, 0, "new",0,0,0,0);
            }
            $rs = $this->updateVIP($pname, "true");
            $ok = true;
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return $ok;
    }

    public function upsetVIP($pname, $status)
    {
        $ok = false;
        try {
            if (!$this->isPlayerExist($pname)) {
                $this->upsetPlayer($pname, $pname, 0, 0, 0, 0, "false", 0, 0, 0, "new",0,0,0,0);
            }
            $rs = $this->updateVIP($pname, $status);
            $ok = true;
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return $ok;
    }

    public function addPlayer($pname)
    {
        $ok = false;
        try {
            if (!$this->isPlayerExist($pname)) {
                $this->upsetPlayer($pname, $pname, 0, 0, 0, 0, "false", 0, 0, 0, "new",0,0,0,0);
            }
            $ok = true;
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return $ok;
    }

    public function isPlayerVIP($pname)
    {
        try {
            if (!$this->isPlayerExist($pname)) {
                $this->upsetPlayer($pname, $pname, 0, 0, 0, 0, "false", 0, 0, 0, "new",0,0,0,0);
                return false;
            }
            $data = $this->retrievePlayerVIP($pname);
            if ($data[0]["vip"] == "true") {
                return true;
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return false;
    }

    public function addPlayerLoss($pname)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    try {
                        $loss = $data ["loss"] + 1;
                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET loss=:loss WHERE pname = :pname");
                        $prepare->bindValue(":loss", $loss, SQLITE3_INTEGER);
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player profile updated!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }
    
    public function addPlayerLossHider($pname)
    {
    	try {
    		$prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
    		$prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
    		$result = $prepare->execute();
    		if ($result instanceof \SQLite3Result) {
    			$data = $result->fetchArray(SQLITE3_ASSOC);
    			$result->finalize();
    			if (isset ($data ["pname"])) {
    				try {
    					$loss_hider = $data ["loss_hider"] + 1;
    					$prepare = $this->plugin->database->prepare("UPDATE player_profile SET loss_hider=:loss_hider WHERE pname = :pname");
    					$prepare->bindValue(":loss_hider", $loss_hider, SQLITE3_INTEGER);
    					$prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
    					$prepare->execute();
    				} catch (\Exception $e) {
    					LogUtil::printLog($this->getPlugIn(), $e);
    					return "data error: " . $e->getMessage();
    				}
    				return "player profile updated!";
    			}
    		}
    	} catch (\Exception $e) {
    		LogUtil::printLog($this->getPlugIn(), $e);
    		return "db error: " . $e->getMessage();
    	}
    	return null;
    }
    
    public function addPlayerLossSeeker($pname)
    {
    	try {
    		$prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
    		$prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
    		$result = $prepare->execute();
    		if ($result instanceof \SQLite3Result) {
    			$data = $result->fetchArray(SQLITE3_ASSOC);
    			$result->finalize();
    			if (isset ($data ["pname"])) {
    				try {
    					$loss_hider = $data ["loss_seeker"] + 1;
    					$prepare = $this->plugin->database->prepare("UPDATE player_profile SET loss_seeker=:loss_seeker WHERE pname = :pname");
    					$prepare->bindValue(":loss_seeker", $loss_hider, SQLITE3_INTEGER);
    					$prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
    					$prepare->execute();
    				} catch (\Exception $e) {
    					LogUtil::printLog($this->getPlugIn(), $e);
    					return "data error: " . $e->getMessage();
    				}
    				return "player profile updated!";
    			}
    		}
    	} catch (\Exception $e) {
    		LogUtil::printLog($this->getPlugIn(), $e);
    		return "db error: " . $e->getMessage();
    	}
    	return null;
    }
    

    public function deposit($pname, $amount)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();

                $this->log("deposit: old balance :" . $data ["balance"]);
                if (isset ($data ["pname"])) {
                    try {
                        $newBalance = $data ["balance"] + $amount;
                        $this->log("deposit: new balance :" . $newBalance);

                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET balance=:balance WHERE pname = :pname");
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->bindValue(":balance", $newBalance, SQLITE3_INTEGER);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "deposit success!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    public function withdraw($pname, $amount)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    $this->log("withdraw: old balance :" . $data ["balance"]);

                    if ($data ["balance"] < $amount) {
                        return "Insufficient fund!";
                    }
                    try {
                        $newBalance = $data ["balance"] - $amount;
                        $this->log("withdraw: new balance :" . $newBalance);

                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET balance=:balance WHERE pname = :pname");
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->bindValue(":balance", $newBalance, SQLITE3_INTEGER);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "withdraw success!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    public function updateVIP($pname, $vip)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname=:pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    try {
                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET vip=:vip WHERE pname = :pname");
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->bindValue(":vip", $vip, SQLITE3_TEXT);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player profile updated!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    public function setBalance($pname, $amount)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname=:pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    try {
                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET balance=:balance WHERE pname = :pname");
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->bindValue(":balance", $amount, SQLITE3_TEXT);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player balance updated!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    public function updatePassword($pname, $newPassword)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname=:pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    try {
                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET password=:password WHERE pname = :pname");
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->bindValue(":password", $newPassword, SQLITE3_TEXT);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player profile updated!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    /**
     * Admin reset password
     *
     * @param unknown $pname
     * @return string|NULL
     */
    public function resetPassword($pname)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname=:pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    try {
                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET password=:password WHERE pname = :pname");
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->bindValue(":password", $pname, SQLITE3_TEXT);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player profile updated!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    /**
     * retrieve all arena names
     *
     * @return string|multitype:Ambigous <>
     */
    public function retrieveAllPlayers()
    {
        $records = [];
        try {
            $prepare = $this->plugin->database->prepare("SELECT * FROM player_profile");
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
                    $records [] = $data;
                }
                $result->finalize();
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return $records;
    }

    public function upsetPlayer($pname, $password, $balance, $rank, $wins, $loss, $vip, $home_x, $home_y, $home_z, $status)
    {
        try {
            $prepare = $this->plugin->database->prepare("SELECT * from player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $result = $prepare->execute();
            if ($result instanceof \SQLite3Result) {
                $data = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
                if (isset ($data ["pname"])) {
                    try {
                        $prepare = $this->plugin->database->prepare("UPDATE player_profile SET password=:password, balance=:balance, rank=:rank, wins=:wins, loss=:loss, vip=:vip, home_x = :home_x, home_y=:home_y, home_z=:home_z, status=:status, win_seeker=:win_seeker, win_hider=:win_hider,loss_seeker=:loss_seeker,loss_hider=:loss_hider WHERE pname = :pname");
                        $prepare->bindValue(":password", $password, SQLITE3_TEXT);
                        $prepare->bindValue(":balance", $balance, SQLITE3_INTEGER);
                        $prepare->bindValue(":rank", $rank, SQLITE3_INTEGER);
                        $prepare->bindValue(":wins", $wins, SQLITE3_INTEGER);
                        $prepare->bindValue(":loss", $loss, SQLITE3_INTEGER);
                        $prepare->bindValue(":vip", $vip, SQLITE3_TEXT);
                        $prepare->bindValue(":home_x", $home_x, SQLITE3_INTEGER);
                        $prepare->bindValue(":home_y", $home_y, SQLITE3_INTEGER);
                        $prepare->bindValue(":home_z", $home_z, SQLITE3_INTEGER);
                        $prepare->bindValue(":status", $status, SQLITE3_TEXT);
                        $prepare->bindValue(":win_seeker", 0, SQLITE3_INTEGER);
                        $prepare->bindValue(":win_hider", 0, SQLITE3_INTEGER);
                        $prepare->bindValue(":loss_seeker", 0, SQLITE3_INTEGER);
                        $prepare->bindValue(":loss_hider", 0, SQLITE3_INTEGER);
                        
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player profile updated!";
                } else {
                    try {
                        $prepare = $this->plugin->database->prepare("INSERT INTO player_profile (pname,password, balance,rank,wins,loss,vip,home_x,home_y, home_z,status, win_seeker, win_hider,loss_seeker,loss_hider) VALUES (:pname,:password, :balance,:rank,:wins,:loss,:vip,:home_x,:home_y,:home_z,:status,:win_seeker, :win_hider,:loss_seeker,:loss_hider)");
                        $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
                        $prepare->bindValue(":password", $password, SQLITE3_TEXT);
                        $prepare->bindValue(":balance", $balance, SQLITE3_INTEGER);
                        $prepare->bindValue(":rank", $rank, SQLITE3_INTEGER);
                        $prepare->bindValue(":wins", $wins, SQLITE3_INTEGER);
                        $prepare->bindValue(":loss", $loss, SQLITE3_INTEGER);
                        $prepare->bindValue(":vip", $vip, SQLITE3_TEXT);
                        $prepare->bindValue(":home_x", $home_x, SQLITE3_INTEGER);
                        $prepare->bindValue(":home_y", $home_y, SQLITE3_INTEGER);
                        $prepare->bindValue(":home_z", $home_z, SQLITE3_INTEGER);
                        $prepare->bindValue(":status", $status, SQLITE3_TEXT);
                        $prepare->bindValue(":win_seeker", 0, SQLITE3_INTEGER);
                        $prepare->bindValue(":win_hider", 0, SQLITE3_INTEGER);
                        $prepare->bindValue(":loss_seeker", 0, SQLITE3_INTEGER);
                        $prepare->bindValue(":loss_hider", 0, SQLITE3_INTEGER);
                        $prepare->execute();
                    } catch (\Exception $e) {
                        LogUtil::printLog($this->getPlugIn(), $e);
                        return "data error: " . $e->getMessage();
                    }
                    return "player profile created!";
                }
            }
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: " . $e->getMessage();
        }
        return null;
    }

    public function removePlayerProfile($pname)
    {
        try {
            $prepare = $this->plugin->database->prepare("DELETE FROM player_profile WHERE pname = :pname");
            $prepare->bindValue(":pname", $pname, SQLITE3_TEXT);
            $prepare->execute();
        } catch (\Exception $e) {
            LogUtil::printLog($this->getPlugIn(), $e);
            return "db error: unable delete profile :" . $e->getMessage();
        }
        return "profile deleted!";
    }
}