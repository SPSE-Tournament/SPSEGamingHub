<?php
  class MessageManager {

    public function returnMessages($userId) {
      return Db::multiQuery("SELECT message_id, message, message_perex, message_type, message_status, message_timestamp, user_senderid, user_receiverid,
        user_sendername, user_receivername from
        messages where user_receiverid = ? order by message_timestamp desc limit 5", array($userId));
    }

    public function returnMessagesByType($userId, $messageType) {
      return Db::multiQuery("SELECT message_id, message, message_perex, message_type, message_status, message_timestamp, user_senderid, user_receiverid,
        user_sendername, user_receivername from
        messages where user_receiverid = ? and message_type = ? order by message_timestamp desc limit 5", array($userId, $messageType));
    }

    public function returnMessageById($mesId) {
      return Db::singleQuery("SELECT message_id, message, message_perex,message_type, message_status message_timestamp, user_senderid, user_receiverid,
      user_sendername, user_receivername from messages where message_id = ?", array($mesId));
    }

    public function sendMessage($message,$messageType,$timestamp,$senderId,$senderName,$receiverId,$receiverName) {
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
          'user_receivername' => $receiverName
        );
        Db::insert('messages', $message);
      } catch (PDOException $e) {
        throw new UserError($e);
      }
    }
  }
?>
