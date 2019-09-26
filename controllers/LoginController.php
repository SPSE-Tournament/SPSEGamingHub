<?php
  class LoginController extends Controller {
    public function parse($params) {
      $userMan = new UserManager();
      if ($userMan->returnUser()) {
        $this->redir("profile");
      }
      if ($_POST) {
        try {
          $userMan->login($_POST['name'], $_POST['pw']);
          $this->addMessage("Byl jste úspěšně přihlášen.");
          $this->log("User log.", "login");
          $this->redir('home');
        } catch (UserError $e) {
          $this->addMessage($e->getMessage());
        }

      }
    $this->view = 'login';
  }
  }



?>
