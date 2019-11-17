<?php
  class EventManager {
      public function returnEvents() {
        return Db::multiQuery("SELECT event_id, event_name, games.game_name as game_name, games.game_id as game_id,games.game_background as game_background, games.game_rules as game_rules, bracket_status,event_winner,
         concat(substr(event_timestamp,9,2), '.', substr(event_timestamp,6,2), '.', substr(event_timestamp,1,4), ' ', substr(event_timestamp,12,5)) as event_parseddate,
         event_playerlimit, games.game_playerlimitperteam as game_plteam, event_timestamp, event_url, (select count(*) from eventparticipation where eventparticipation.event_id = events.event_id) as player_count
                                from events
                                join games on events.game_id = games.game_id
                                order by event_timestamp");
      }

      public function returnEventByUrl($url) {
        return Db::singleQuery("SELECT event_id, event_name, games.game_name as game_name, games.game_id as game_id,games.game_background as game_background,games.game_rules as game_rules, bracket_status, event_winner,(select team_name from teams where team_id = event_winner)as event_winner_name,
         concat(substr(event_timestamp,9,2), '.', substr(event_timestamp,6,2), '.', substr(event_timestamp,1,4), ' ', substr(event_timestamp,12,5)) as event_parseddate,
         event_playerlimit, games.game_playerlimitperteam as game_plteam, event_timestamp, event_url
                                from events
                                join games on events.game_id = games.game_id
                                where event_url = ?", array($url));
      }

      public function returnEventById($eventId) {
        return Db::singleQuery("SELECT event_id, event_name, games.game_name as game_name, games.game_id as game_id,games.game_background as game_background,games.game_rules as game_rules, bracket_status,event_winner,
         concat(substr(event_timestamp,9,2), '.', substr(event_timestamp,6,2), '.', substr(event_timestamp,1,4), ' ', substr(event_timestamp,12,5)) as event_parseddate,
         event_playerlimit, games.game_playerlimitperteam as game_plteam, event_timestamp, event_url
                                from events
                                join games on events.game_id = games.game_id
                                where event_id = ?", array($eventId));
      }

      public function returnTeamIdsInEvent($eventId) {
        return Db::multiQuery("SELECT distinct team_id from eventparticipation where event_id = ?", array($eventId));
      }


      public function createEvent($name, $game, $timestamp, $eventPL, $gamePL, $eventUrl) {
        $event = array(
          'event_name' => $name,
          'game_id' => $game,
          'event_timestamp' => $timestamp,
          'event_playerlimit' => $eventPL,
          'game_playerlimitperteam' => $gamePL,
          'event_url' => $eventUrl
        );
        try {
          Db::insert('events', $event);
        } catch (PDOException $e) {
          throw new UserError($e);
        }
      }

      public function insertEventParticipation($userId, $eventId, $teamId) {
        $eventParticipation = array(
          'user_id' => $userId,
          'event_id' => $eventId,
          'team_id' => $teamId
        );
        try {
            Db::insert('eventparticipation', $eventParticipation);
        } catch (PDOException $e) {
          throw new UserError($e);
        }
      }

      public function setLiveBracketStatus($eventId) {
        try {
          Db::query("UPDATE events set bracket_status = 'live', event_timestamp = event_timestamp where event_id = ?", array($eventId));
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

      public function deleteEvent($eventId) {
        Db::query("DELETE from events where event_id = ?", array($eventId));
      }

      public function getGameLimit($gameid) {
        return Db::singleQuery("select game_playerlimitperteam from games where game_id = ?", array($gameid));
      }



  }


?>
