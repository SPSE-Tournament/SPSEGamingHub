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
          #  $this->data['messages'] = $mesMan->returnMessages($_SESSION['user_id']); 
            $this->view = 'messages';
        } else {
          $this->data['usrname'] = $_SESSION['user']['name'];
          $this->data['email'] = $_SESSION['user']['email'];
          $this->data['admin'] = $_SESSION['user']['admin'];
          $this->view = 'profile';
        }
      }


  }

?>
