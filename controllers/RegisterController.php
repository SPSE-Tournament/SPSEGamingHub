<?php
class RegisterController extends Controller
{
  public function parse($params)
  {
    $this->header['page_title'] = 'Registration';
    $userMan = new UserManager();
    $validationManager = new ValidationManager();

    if (!empty($params[0]) && $params[0] == 'verify') {
      $registrationHashes = $userMan->returnRegistrationHashes();
      if (!empty($params[1]) && in_array($params[1], $registrationHashes)) {
        $reg = $userMan->returnRegistrationByHash($params[1]);
        try {
          $email = explode("@", $reg['user_email']);
          $verification = 0;
          if ($email[1] == 'zaci.spse.cz' || $email[1] == 'spse.cz') {
            $verification = 1;
          }
          $userMan->register($reg['user_name'], $reg['user_email'], $reg['user_password'], $userMan->generateHexId(), $verification);
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
        $emails = $userMan->returnEmails();
        $unames = $userMan->returnUsernames();
        $tests = [
          "Fields empty" => $validationManager->notEmpty($_POST),
          "Invalid email" => filter_var($_POST['email'], FILTER_VALIDATE_EMAIL),
          "Password must be at least 5 characters long" => $validationManager->min($_POST['pw'], 5),
          "Username must be at least 4 characters long" => $validationManager->min($_POST['usrname'], 4),
          "Username in a wrong format" => $validationManager->username($_POST['usrname']),
          "User already exists" => !in_array($_POST['usrname'], $unames) && !in_array($_POST['email'], $emails),
          "You must agree with the terms of service" => isset($_POST['agreement-tos']) && $_POST['agreement-tos']
        ];
        $validationManager->validate($tests);
        $userMan->requestRegister($_POST['usrname'], $_POST['email'], $_POST['pw'], $_POST['pwA'], $_POST['antispam']);
        $this->addMessage("Your request noted, we have sent you a verification email.");
        $this->redir('register/verify');
      } catch (ValidationError | PDOException | UserError $e) {
        $this->addMessage(ExceptionHandler::getMessage($e));
        $this->refresh();
      }
    }
  }
}
