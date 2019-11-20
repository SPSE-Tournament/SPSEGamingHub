<?php
  class RegisterController extends Controller {
    public function parse($params) {
      $this->header['page_title'] = 'Registration';
      $userMan = new UserManager();

      //Handling POST
      if ($_POST) {
        try {
          if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $emails = $userMan->returnEmails();
            $unames = $userMan->returnUsernames();
                if ($_POST['agreement-tos']) {
                  if (!in_array($_POST['usrname'], $unames) && !in_array($_POST['email'], $emails)) {
                    $userManager = new UserManager();
                    $userManager->requestRegister($_POST['usrname'], $_POST['email'], $_POST['pw'], $_POST['pwA'], $_POST['antispam']);
                    $this->addMessage("Your request noted, we have sent you a verification email just to really know it's you.");
                    $this->log("User registered.", "register");
                    $this->redir('profile/verify');
                  } else {
                    $this->addMessage("Email or username already exists");
                    $this->redir("register");
                  }

                } else {
                  $this->addMessage("You must agree with the terms of service.");
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
