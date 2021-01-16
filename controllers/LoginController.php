<?php
class LoginController extends Controller
{
  public function parse($params)
  {
    $userMan = new UserManager();
    $validationManager = new ValidationManager();
    if ($userMan->returnUser()) {
      $this->redir("profile");
    }

    //Handling POST
    if ($_POST) {
      try {
        $tests = [
          "Fields empty" => $validationManager->notEmpty($_POST),
        ];
        $validationManager->validate($tests);
        $userMan->login($_POST['name'], $_POST['pw']);
        $this->addMessage("Logged in.");
        $this->log("User log.", "login");
        $this->redir('home');
      } catch (ValidationError | UserError $e) {
        $this->addMessage(ExceptionHandler::getMessage($e));
        $this->refresh();
      }
    }
    $this->view = 'login';
  }
}
