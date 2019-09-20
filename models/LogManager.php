<?php
  class LogManager {

    public function returnLogs() {
      return Db::multiQuery("select log_id, logs.user_id as uid, users.name as uname, message, type, message_timestamp, user_ip from logs
      join users on logs.user_id = users.user_id order by message_timestamp");
    }

    public function log($userid, $msg, $type, $timestamp, $ip) {
      $log = array(
        'user_id' => $userid,
        'message' => $msg,
        'type' => $type,
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
