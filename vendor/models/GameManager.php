<?php
  class GameManager {

    public function returnGames() {
      return Db::multiQuery("SELECT game_id, game_name,game_short_name, game_rules, game_playerlimitperteam,game_background,game_icon from games order by game_id");
    }

    public function returnGameById($gameId) {
      return Db::singleQuery("SELECT game_id, game_name,game_short_name, game_rules, game_playerlimitperteam,game_background,game_icon from games where game_id = ?", array($gameId));
    }

    public function addGame($gameName,$shortName, $gameTL, $gameBck = null, $gameRules = null, $gameIcon = null) {
      $game = array(
        'game_name' => $gameName,
        'game_short_name' => $shortName,
        'game_rules' => $gameRules,
        'game_playerlimitperteam' => $gameTL,
        'game_background' => $gameBck,
        'game_icon'=>$gameIcon
      );
      try {
      Db::insert('games', $game);
    } catch (PDOException $e) {
      throw new UserError($e);
    }
    }

    public function editGame($gameName,$shortName, $gameTL, $gameBck = null, $gameRules = null,$gameIcon = null, $gameId) {
      $curGame = $this->returnGameById($gameId);
      $game = array(
        'game_name' => $gameName,
        'game_short_name' => $shortName,
        'game_rules' => $gameRules,
        'game_playerlimitperteam' => $gameTL,
        'game_background' => $gameBck,
        'game_icon'=>$gameIcon
      );
      if (!isset($gameBck)) {
        $game['game_background'] = $curGame['game_background'];
      } else {
        $game['game_background'] = $gameBck;
      }
      if (!isset($gameRules)) {
        $game['game_rules'] = $curGame['game_rules'];
      } else {
        $game['game_rules'] = $gameRules;
      }
      if (!isset($gameIcon)) {
        $game['game_icon'] = $curGame['game_icon'];
      } else {
        $game['game_icon'] = $gameIcon;
      }
      Db::edit('games', $game, 'where game_id = ?', array($gameId));
    }

    public function deleteGame($gameId){
      Db::query("DELETE from games where game_id = ?", array($gameId));
    }

    public function getGameIds() {
      $games = $this->returnGames();
      $gameIds = array();
      foreach ($games as $game) {
        $gameIds[] = $game['game_id'];
      }
      return $gameIds;
    }

  }



?>
