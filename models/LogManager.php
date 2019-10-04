<?php
  class LogManager {

    public function returnLogs() {
      return Db::multiQuery("select log_id, logs.user_id as uid, users.name as uname, log_message, log_type, log_timestamp, log_userip from logs
      join users on logs.user_id = users.user_id order by log_timestamp desc");
    }

    public function log($userid, $msg, $type, $timestamp, $ip) {
      $log = array(
        'user_id' => $userid,
        'log_message' => $msg,
        'log_type' => $type,
        'log_timestamp' => $timestamp,
        'log_userip' => $ip
      );
      try {
        Db::insert('logs', $log);
      } catch (PDOException $e) {
        throw new UserError($e);
      }

    }


  }

?>
