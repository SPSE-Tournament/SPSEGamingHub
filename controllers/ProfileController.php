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

        if (!empty($params[0]) && $params[0] == 'logout') {

            $this->log("User logout.", "login");
            $userMan->logout();
            $this->addMessage("Byl jste úspěšně odhlášen.");
            $this->redir("login");

        } if (!empty($params[0]) && $params[0] == 'messages') {

           $this->data['messages'] = $mesMan->returnMessages($_SESSION['user_id']);
            $this->view = 'messages';
        }
        else if (!empty($params[0]) && $params[0] == 'getusers') {
          if (!empty($params[1])) {
            $str = $params[1];
          } else {
            $str = "";
          }
          $response = $userMan->liveSearchUsers($str);
          $this->data['response'] = $response;
          $this->view = 'userlist';
        } else {
          $this->data['user'] = $_SESSION['user'];
          $this->data['profile'] = "";
          $this->view = 'profile';
        }
      }
  }
?>
