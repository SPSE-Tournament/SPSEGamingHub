<?php
  class ProfileController extends Controller {
      public function parse($params) {
        $userMan = new UserManager();
        $mesMan = new MessageManager();
        $date = new DateTime();

        if (isset($_POST['message-add'])) {
          $parsedHexName = $userMan->parseHexname($_POST['receiver']);
          $receiverId = $userMan->selectUser($parsedHexName['name']);
          $mesMan->sendMessage($_POST['message'], $date->format('Y-m-d H:i:s'),$_SESSION['user']['user_id'],$_SESSION['user']['name'],$receiverId['user_id'],$parsedHexName['name']);
          $this->addMessage("Your message has been sent.");
          $this->log("Message sent. Sender: ".$_SESSION['user']['user_id'].', Receiver: '. $_POST['receiver'] . ", Message: ". $_POST['message'] ,'message_sent');
          $this->redir("profile");
      }

        if (!empty($params[0])) {

          if ($params[0] == 'logout') {

              $this->log("User logout.", "login");
              $userMan->logout();
              $this->addMessage("Byl jste úspěšně odhlášen.");
              $this->redir("login");

          } if ($params[0] == 'messages') {
            $messages = $mesMan->returnMessages($_SESSION['user']['user_id']);
            $messageIds = array();
            for ($i=0; $i < count($messages); $i++) {
                  $messageIds[] = $messages[$i]['message_id'];
            }
            $this->data['messages'] = $messages;
            $this->data['date'] = new DateTime("now");
            $this->view = 'messages';
          }
          else if($params[0] == 'getmessage') {
            $messages = $mesMan->returnMessages($_SESSION['user']['user_id']);
            $messageIds = array();
            for ($i=0; $i < count($messages); $i++) {
                  $messageIds[] = $messages[$i]['message_id'];
            }
            if (!empty($params[1]) && in_array($params[1], $messageIds)) {
              $this->data['message'] = $mesMan->returnMessageById($params[1]);
              $this->view = 'message';
            }

          }
          else if ($params[0] == 'getusers') {
            if (!empty($params[1])) {
              $str = $params[1];
            } else {
              $str = "";
            }
            $response = $userMan->liveSearchUsers($str);
            $this->data['response'] = $response;
            $this->view = 'userlist';
          }
        } else {
          $this->data['user'] = $_SESSION['user'];
          $this->data['profile'] = "";
          $this->view = 'profile';
        }


      }
  }
?>
