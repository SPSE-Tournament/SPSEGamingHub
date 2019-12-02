<?php
  class MatchesManager {
    public function dropMatches($eventId) {
      Db::query("DELETE from matches where event_id = ?", array($eventId));
    }
  }






 ?>
