<?php
  class MessageManager {

    public function returnMessages($userId):array {
      return Db::multiQuery("SELECT message_id, message, message_perex, message_type, message_status, message_timestamp, user_senderid, user_receiverid,
        user_sendername, user_receivername, invite_team_id from
        messages where user_receiverid = ? order by message_timestamp desc limit 5", array($userId));
    }

    public function returnMessagesByType($userId, $messageType):array {
      $msgs =  Db::multiQuery("SELECT message_id, message, message_perex, message_type, message_status, message_timestamp, timediff(NOW(),message_timestamp) as time_ago, user_senderid, user_receiverid,
        user_sendername, user_receivername, invite_team_id from
        messages where user_receiverid = ? and message_type = ? order by message_timestamp desc limit 5", array($userId, $messageType));
      $realMsgs = array();
        foreach ($msgs as $m) {
          $e = explode(":", (String)$m['time_ago']);
          if ((int)$e[0] > 24) {
            $daysAgo = floor((int)$e[0]/24);
            if ($daysAgo != 1)
              $m['time_ago'] = (String)$daysAgo . " Days ago";
            else
              $m['time_ago'] = (String)$daysAgo . " Day ago";
          } else if ((int)$e[0] >= 1) {
            if (substr((String)$e[0],0,1) == "0")
              $m['time_ago'] = substr($e[0],1,1) . " Hours ago";
            else
              $m['time_ago'] = $e[0] . " Hours ago";
            if (substr($e[0],0,2) == "01")
              $m['time_ago'] = substr($e[0],1,1) . " Hour ago";
          } else if ((int)$e[0] < 1) {
              if (substr($e[1],0,2) == "01")
                $m['time_ago'] = substr($e[1],1,1) . " Minute ago";
              else if (substr($e[1],0,2) == "00")
              $m['time_ago'] = " Less than a minute ago";
              else
                $m['time_ago'] = substr($e[1],0,2) . " Minutes ago";
          }
          $realMsgs[] = $m;
        }
      return $realMsgs;
    }

    public function returnMessageById($mesId):array {
      return Db::singleQuery("SELECT message_id, message, message_perex,message_type, message_status message_timestamp, user_senderid, user_receiverid,
      user_sendername, user_receivername, invite_team_id from messages where message_id = ?", array($mesId));
    }

    public function deleteMessageById($messId) {
      Db::query("DELETE from messages where message_id = ?", array($messId));
    }

    public function sendMessage($message,$messageType,$timestamp,$senderId,$senderName,$receiverId,$receiverName, $teamId = null) {
      $messagePerex = substr($message, 0, 15) . "...";
      try {
        $message = array(
          'message' => $message,
          'message_perex' => $messagePerex,
          'message_type' => $messageType,
          'message_timestamp' => $timestamp,
          'user_senderid' => $senderId,
          'user_sendername' => $senderName,
          'user_receiverid' => $receiverId,
          'user_receivername' => $receiverName,
          'invite_team_id' => $teamId
        );
        Db::insert('messages', $message);
      } catch (PDOException $e) {
        throw new UserError($e);
      }
    }
  }
?>
