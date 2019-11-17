<?php
  class RegisterController extends Controller {
    public function parse($params) {
      $this->header['page_title'] = 'Registration';
      $userMan = new UserManager();

      //Handling POST
      if ($_POST) {
        try {
          if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $year = date("Y");
            $emails = $userMan->returnEmails();
            $unames = $userMan->returnUsernames();
            if ($_POST['antispam'] == $year) {
              if ($_POST['pw'] == $_POST['pwA']) {
                if ($_POST['agreement-tos']) {
                  if (!in_array($_POST['usrname'], $unames) && !in_array($_POST['email'], $emails)) {
                    $userManager = new UserManager();
                    $userManager->register($_POST['usrname'], $_POST['email'], $_POST['pw'], $_POST['pwA'], $_POST['antispam'], $userManager->generateHexId());
                    $userManager->login($_POST['usrname'], $_POST['pw']);
                    $this->addMessage("Byl jste úspěšně zaregistrován");
                    $this->log("User registered.", "register");
                    $this->redir('profile');
                  } else {
                    $this->addMessage("Email or username already exists");
                    $this->redir("register");
                  }

                } else {
                  $this->addMessage("You must agree with the terms of service in order to register.");
                  $this->redir("register");
                }
              } else {
                $this->addMessage("Passwords don't match!");
                $this->redir("register");
              }
            } else {
              $this->addMessage("Invalid antispam!");
              $this->redir("register");
            }
          } else {
            $this->addMessage("Invalid email!");
            $this->redir("register");
          }

        } catch (UserError $e) {
          $this->addMessage($e->getMessage());
        }

      }
      $this->view = 'register';
    }
  }


?>
