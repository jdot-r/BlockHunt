Block Hunt v2.2.0 changes
[Enhancements]
1. prevent not-in-game player going inside arena during game play
2. add support of arena backup and restore commands
3. add support of auto-reset arena on game start  [make sure backup your maps first]

[Fixes]
1. tap in arena exit sign didn't remove hider block
2. use [/bh stat] command result sql error.
3. missed count player game loss when player disconnect or kicked or leave the game
4. If PM world loading tooks longer than start of sign update result error. (happen on overload server)
5. player spawn inside arena when disconnected during game play
6. use [/bh home] command didn't released in-game player equipments
7. when use both auto-join and specific roles join sign. game start count is incorrect.


BlockHunt v2.1.5 Fixes

- add arena reset feature during game count down
- reset is based on snapshot of the arena blocks up to 10 blocks above


This is a brand new minecraftgenius76 original MCPE mini-game. Hide and Seek is a game that requires the skillset that only real ninjas have. You need to be able to connect your inner-steve to the world around you. When you join Hide and Seek, you'll be given the option to join as Seeker (aka. Hunter) or Hider (aka. Block).  Once minimal number of players met, the game start automatically by announce a count down.  Once coun down reach to zero.  Seeker get teleport into a seeker room, lock down in the room about 20 to 30 seconds depends on prefer configuration. All hunters have standard armors and a sword.   

Hider also get teleport to the map; by default, hider where pre-assigned with a random block supported the by map and a wood sword to guard against seekers. Hider can choose own block by open inventory item then held the item on hand.  do a small movement, hider will see their block changed in-fly.  to start hider have a grace period to find hiding spot. Hurry up, hider need to hide before seekers (aka. hunters) get released. Hider should find a spot that is blend into environment.  think smart, make sure you look like you're meant to be there!

once hunter get released, they will seek each hider and kill all of them before time-out. when a seeker attack a hider block, hider temporary show up. suggest run as fast you can if hider found by seeker.  If hider been kill by seeker. the hider get converted as seekers. 

Hider win if only one left before time-out. if no hider left, seekers win the game with 3 coins.   coins can be use to purchase food item from mini-shop. so, you never feel hungry while continue playing this intense mini-game.  

Thanks for choosing MinecraftGenius76 Mincraft Pocket Edition Mini-Games.  

Happy hiding! (or seeking)


[MCG76 BlockHunt Features]

- come with a full pre-build gaming map that is ready to play

- customiazabl seeker or hider kits with any amours, or items

- customizable supported arena blocks for hiders

- built-in avoidance of same team players hitting each other

- built-in avoidance players hurting each other in game center area

- in-fly block change for hiders by select item on hand

- fully automate gaming start and stop based on players

- fully configurable following game play parameters
•time the play finish
•time to release seekers
•minimal players to start the game
•maximum players allow for the arena
•blocks allow to use for the arena

- built-in stand alone mini-shop with beautiful caseitems

- built-in stand alone player profile management to keep scores

- built-in protecting for gaming center to avoid player to break or place block other than op

- built-in protection for arena border, where player not allow to reach outside while game is playing

- built-in automatic door opening for seekers

- fully customizable to allow player use own Hide and Seek from scratch use own map
•create new arena
•set arena positions
•set arena seeker spawn location
•set arena hider spawn location
•set arena seeker door positions
•set seeker join signs
•set hider join sign
•set arena stats sign
•set arena exit sign  
•set arena allowable blocks
•create new protected area
•set protected area

 - fully customizable to allow player use own map

 
[Installation Requirement]

Latest Free version of Pocketmine server alpha 1.4 (http://www.pocketmine.net/) for MCPE 0.10.x for your prefer desktop os or android phone

Installation Steps

pre-requisite is have Pocketmine server installed.

Download and unzip mcg76_blockhunt_minigame.zip file. this zip file has 3 files.
•mcg76_BlockHunt.zip  --  the prebuild plugin configuration files
•mcg76_blockhunt_home.zip -- this is the mini-game map
•mcg76_BlockHunt_v1.0.5.phar -- this is the mini-game plugin file

Next, just drop and unzip the files into the proper location then start your server and it's ready to play. it's that simple.

1. copy mcg76_BlockHunt_v1.0.5.phar  plugin file to your pocketmine server /plugins folder  

   once file copied you should have something like this  "your pocketmine server / plugins / mcg76_BlockHunt_v1.0.5.phar "

2. unzip mcg76_blockhunt_home.zip   mini-game map to your pocketmine server /worlds folder.  

    once unzip it you should have something like this  "your pocketmine server / worlds / mcg76_blockhunt_home "

3. unzip mcg76_BlockHunt.zip  plugin configuration for the map to your pocketmine server /plugins folder.  

    once unzip it you should have something like this  "your pocketmine server / worlds / mcg76_blockhunt "

4. update your server.properties file to use this mini-game map on startup. to do this you need open server.properties file using a text editor and change following:

    level-name=mcg76_blockhunt_home

    level-type=flat

   next, save the changes.   for android user, you have to hit advance button to make the server property changes.  

5. restart your server, wait for couple seconds to load up.

6. Open you mobile phone or table MCPE. then click [Edit] button on top. now you should see [External] button. click the external button and enter your pocketmine server IP. left the default port if you didn't make any change. 

7.  You are good to go.  once you are login you should able see BlockHunt mini-game game center. 

8.  Have fun. enjoy Hidding or Seeking

 

Note:

For installation of pocketmine server, please follow pocketmine installation instruction for your operating system or check out my youtube video on installations.

to put the game on multi-craft hosting, please see instruction for deploying MCPE mini-game in multicraft video. 
 
 