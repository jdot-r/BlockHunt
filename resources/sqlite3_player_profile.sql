CREATE TABLE player_profile (
  pname TEXT PRIMARY KEY,
  password TEXT,
  balance INTEGER,
  rank INTEGER,
  wins INTEGER,  
  loss INTEGER,  
  rating INTEGER,
  vip TEXT,
  home_x INTEGER,
  home_y INTEGER,
  home_z INTEGER,
  status TEXT,
  win_seeker INTEGER,
  win_hider INTEGER,
  loss_seeker INTEGER,
  loss_hider INTEGER
);