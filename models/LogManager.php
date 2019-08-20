<?php
  class LogManager {

    public function log($userid, $msg, $timestamp, $ip) {
      $log = array(
        'user_id' => $userid,
        'message' => $msg,
        'message_timestamp' => $timestamp,
        'user_ip' => $ip
      );
      try {
        Db::insert('logs', $log);
      } catch (PDOException $e) {
        throw new UserError($e);
      }

    }


  }

?>
