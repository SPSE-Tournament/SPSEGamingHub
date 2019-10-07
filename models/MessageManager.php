<?php
  class MessageManager {

    public function returnMessages($userId) {
      return Db::multiQuery("SELECT message_id, message, message_timestamp, user_senderid, user_receiverid,
        user_sendername, user_receivername from
        messages where user_receiverid = ? order by message_timestamp", array($userId));
    }

    public function returnMessageById($mesId) {
      return Db::singleQuery("SELECT message_id, message, message_timestamp, user_senderid, user_receiverid,
      user_sendername, user_receivername from messages where message_id = ?", array($mesId));
    }

    public function sendMessage($message,$timestamp,$senderId,$senderName,$receiverId,$receiverName) {
      try {
        $message = array(
          'message' => $message,
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
