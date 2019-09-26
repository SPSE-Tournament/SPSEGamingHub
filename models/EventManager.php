<?php
  class EventManager {
      public function returnEvents() {
        return Db::multiQuery("SELECT event_id, event_name, games.game_name as game_name, games.game_id as game_id,
         concat(substr(event_timestamp,9,2), '.', substr(event_timestamp,6,2), '.', substr(event_timestamp,1,4), ' ', substr(event_timestamp,12,5)) as event_parseddate,
         event_playerlimit, games.game_playerlimitperteam as game_plteam, event_timestamp, event_url
                                from events
                                join games on events.game_id = games.game_id
                                order by event_timestamp");
      }

      public function returnEventByUrl($url) {
        return Db::singleQuery("SELECT event_id, event_name, games.game_name as game_name, games.game_id as game_id,
         concat(substr(event_timestamp,9,2), '.', substr(event_timestamp,6,2), '.', substr(event_timestamp,1,4), ' ', substr(event_timestamp,12,5)) as event_parseddate,
         event_playerlimit, games.game_playerlimitperteam as game_plteam, event_timestamp, event_url
                                from events
                                join games on events.game_id = games.game_id
                                where event_url = ?
                                order by event_timestamp", array($url));
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

      public function updateEvent($eventName,$eventGame,$eventDate,$eventTime,$eventPL,$eventUrl,$currentUrl) {
        $editedEvent = array(
          'event_name' => $eventName,
          'game_id' => $eventGame,
          'event_timestamp' => $eventDate . ' ' . $eventTime,
          'event_playerlimit' => $eventPL,
          'event_url' => $eventUrl
        );
        try {
          Db::edit('events', $editedEvent, 'where event_url = ?', array($currentUrl));
        } catch (PDOException $e) {
          $this->addMessage($e);
        }
      }

      public function getGameLimit($gameid) {
        return Db::singleQuery("select game_playerlimitperteam from games where game_id = ?", array($gameid));
      }



  }


?>
