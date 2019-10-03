<?php
  class MessageManager {

    public function returnMessages($userId) {
      return Db::multiQuery("SELECT message_id, message, message_timestamp, user_senderid, user_receiverid from
        messages where user_receiverid = ? order by message_timestamp;", array($userId));
    }



  }
?>
