<?php
  class LogManager {

    public $columns = ["log_id", "uid", "uname", "log_type","log_timestamp", "log_userip"];

    public function returnLogs($limit = 30) {
      return Db::multiQuery("SELECT log_id, logs.user_id as uid, users.name as uname, log_message, log_type, log_timestamp, log_userip from logs
      join users on logs.user_id = users.user_id order by log_timestamp desc limit $limit");
    }

    public function returnLogById($id) {
      return Db::singleQuery("SELECT log_id, logs.user_id as uid, users.name as uname, log_message, log_type, log_timestamp, log_userip from logs
      join users on logs.user_id = users.user_id where log_id = ? order by log_timestamp desc", array($id));
    }

    public function returnLogsOrderBy(string $order, string $direction = "desc", $limit = 30) {
      return Db::multiQuery("SELECT log_id, logs.user_id as uid, users.name as uname, log_message, log_type, log_timestamp, log_userip from logs
      join users on logs.user_id = users.user_id order by $order $direction limit $limit");
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
