<?php
  class RegisterController extends Controller {
    public function parse($params) {
      $this->header['page_title'] = 'Registration';
      if ($_POST) {

        try {
            $userManager = new UserManager();
            $userManager->register($_POST['usrname'], $_POST['email'], $_POST['pw'], $_POST['pwA'], $_POST['antispam'], $userManager->generateHexId());
            $userManager->login($_POST['usrname'], $_POST['pw']);
            $this->addMessage("Byl jste úspěšně zaregistrován");
            $this->log("User registered.", "register");
            $this->redir('profile');
        } catch (UserError $e) {
          $this->addMessage($e->getMessage());
        }

      }
      $this->view = 'register';
    }
  }


?>
