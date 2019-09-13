<?php
  class EventManager {
      public function returnEvents() {
        return Db::multiQuery("select event_id, event_name, event_gamename, event_timestamp, event_playerlimit
        from events order by event_timestamp");
      }

      public function createEvent() {
        $event = array(
          'event_name' => $name,
          'event_gamename' => $game,
          'event_timestamp' => $timestamp,
          'event_playerlimit' => $plimit
        );
        try {
          Db::insert('events', $event);
        } catch (PDOException $e) {
          throw new UserError($e);
        }
      }



  }


?>
