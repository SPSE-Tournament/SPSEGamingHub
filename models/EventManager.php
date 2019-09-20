<?php
  class EventManager {
      public function returnEvents() {
        return Db::multiQuery("select event_id, event_name, game_id, event_timestamp, event_playerlimit, game_playerlimitperteam
        from events order by event_timestamp");
      }

      public function createEvent($name, $game, $timestamp, $eventPL, $gamePL) {
        $event = array(
          'event_name' => $name,
          'game_id' => $game,
          'event_timestamp' => $timestamp,
          'event_playerlimit' => $eventPL,
          'game_playerlimitperteam' => $gamePL
        );
        try {
          Db::insert('events', $event);
        } catch (PDOException $e) {
          throw new UserError($e);
        }
      }

      public function getGameLimit($gameid) {
        return Db::singleQuery("select game_playerlimitperteam from games where game_id = ?", array($gameid));
      }



  }


?>
