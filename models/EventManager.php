<?php
  class EventManager {
      public function returnEvents() {
        return Db::multiQuery("select event_id, event_name, games.game_name as game_name,
         concat(substr(event_timestamp,9,2), '.', substr(event_timestamp,6,2), '.', substr(event_timestamp,1,4), ' ', substr(event_timestamp,12,5)) as event_parseddate, 
         event_playerlimit, games.game_playerlimitperteam as game_plteam
                                from events
                                join games on events.game_id = games.game_id
                                order by event_timestamp");
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
