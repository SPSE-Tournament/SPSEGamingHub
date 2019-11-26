<?php
  class RegisterController extends Controller {
    public function parse($params) {
      $this->header['page_title'] = 'Registration';
      $userMan = new UserManager();

      if (!empty($params[0]) && $params[0] == 'verify') {
          $registrationHashes = $userMan->returnRegistrationHashes();
            if (!empty($params[1]) && in_array($params[1], $registrationHashes)) {
              $reg = $userMan->returnRegistrationByHash($params[1]);
              try {
                $email = explode("@",$reg['user_email']);
                $verification = 0;
                if ($email[1] == 'zaci.spse.cz' || $email[1] == 'spse.cz') {
                  $verification = 1;
                }
              $userMan->register($reg['user_name'],$reg['user_email'],$reg['user_password'],$userMan->generateHexId(), $verification);
              Db::query("DELETE from registrations where user_hash = ?", array($params[1]));
              $this->addMessage("Your email has been verified, please log-in");
              $this->redir("login");
            } catch (PDOException $e) {
              $this->addMessage($e);
          }
        } else if (empty($params[1]))
          $this->view = "verification";
      } else {
        $this->view = 'register';
      }

      //Handling POST
      if ($_POST) {
          try {
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
              $emails = $userMan->returnEmails();
              $unames = $userMan->returnUsernames();
                  if ($_POST['agreement-tos']) {
                    if (!in_array($_POST['usrname'], $unames) && !in_array($_POST['email'], $emails)) {
                      if (strlen($_POST['usrname']) > 3) {
                        if (strlen($_POST['pw']) > 4) {
                          $userManager = new UserManager();
                          $userManager->requestRegister($_POST['usrname'], $_POST['email'], $_POST['pw'], $_POST['pwA'], $_POST['antispam']);
                          $this->addMessage("Your request noted, we have sent you a verification email just to really know it's you.");
                          $this->redir('register/verify');
                        } else {
                          $this->addMessage("Password must be atleast 5 characters long");
                        }
                      } else {
                        $this->addMessage("Username must be atleast 4 characters long");
                      }

                    } else {
                      $this->addMessage("Email or username already exists");
                    }
                  } else {
                    $this->addMessage("You must agree with the terms of service.");
                  }
            } else {
              $this->addMessage("Invalid email!");
            }

          } catch (UserError $e) {
            $this->addMessage($e->getMessage());
          }
      }
    }
  }


?>
