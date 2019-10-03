<?php
  class ProfileController extends Controller {
      public function parse($params) {
        if (!empty($params[0]) && $params[0] == 'logout') {
            $userMan = new UserManager();
            $mesMan = new MessageManager();
            $this->log("User logout.", "login");
            $userMan->logout();
            $this->addMessage("Byl jste úspěšně odhlášen.");
            $this->redir("login");
        } if (!empty($params[0]) && $params[0] == 'messages') {
        //  if ($_POST['send_message']) {}
           #$this->data['messages'] = $mesMan->returnMessages($_SESSION['user_id']);
            $this->view = 'messages';
        } else if (!empty($params[0]) && $params[0] == 'getusers') {
          if (!empty($params[1])) {
            $str = $params[1];
          } else {
            $str = "";
          }
          $users = $userMan->liveSearchUsers($str);
          $this->view = 'userlist';
        } else {
          $this->data['user'] = $_SESSION['user'];
          $this->data['profile'] = "";
          $this->view = 'profile';
        }
      }
  }
?>
