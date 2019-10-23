<?php
  class GameManager {

    public function returnGames() {
      return Db::multiQuery("SELECT game_id, game_name, game_rules, game_playerlimitperteam from games order by game_id");
    }

    public function returnGameById($gameId) {
      return Db::singleQuery("SELECT game_id, game_name, game_rules, game_playerlimitperteam from games order by game_id");
    }

    public function addGame($gameName, $gameTL, $gameRules = null, $gameBck = null) {
      $game = array(
        'game_name' => $gameName,
        'game_rules' => $gameRules,
        'game_playerlimitperteam' => $gameTL,
        'game_background' => $gameBck,
      );
      try {
      Db::insert('games', $game);
    } catch (PDOException $e) {
      throw new UserError($e);
    }
    }

  }



?>
