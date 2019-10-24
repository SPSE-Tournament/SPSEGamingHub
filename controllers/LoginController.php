<?php
  class LoginController extends Controller {
    public function parse($params) {
      $userMan = new UserManager();
      if ($userMan->returnUser()) {
        $this->redir("profile");
      }
      if ($_POST) {
        try {
          if (strlen($_POST['name']) > 0 && strlen($_POST['pw']) > 0) {
            $userMan->login($_POST['name'], $_POST['pw']);
            $this->addMessage("Byl jste úspěšně přihlášen.");
            $this->log("User log.", "login");
            $this->redir('home');
          } else {
            $this->addMessage("Fields can't be empty!");
            $this->redir("login");
          }

        } catch (UserError $e) {
          $this->addMessage($e->getMessage());
        }

      }
    $this->view = 'login';
  }
  }



?>
