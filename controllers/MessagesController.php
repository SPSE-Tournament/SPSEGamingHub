<?php
class MessagesController extends Controller
{

  public function parse($params)
  {
    $mesMan = new MessageManager();
    $teamMan = new TeamManager();
    $messageManager = new MessageManager();
    $userManager = new UserManager();
    $validationManager = new ValidationManager();

    if (!empty($params)) {
      $m = $messageManager->returnMessageById($params[0]);
      if ($m['user_receiverid'] == $userManager->returnUser()['user_id']) {
        $this->view = "message";
        $this->data['message'] = $m;
      } else {
        HTTP::status(403);
        $this->redir("status/403");
      }
    } else if ($userManager->returnUser()) {
      $this->view = "messages";
    } else {
      HTTP::status(403);
      $this->redir("status/403");
    }

    if (isset($_POST)) {
      if (isset($_POST['send-msg'])) {
        if (empty($_POST['recipient']))
          $this->refresh();
        $parsedHexName = $userManager->parseHexname($_POST['recipient']);
        try {
          $tests = [
            "Message empty." => !empty($_POST['message']),
            "Recipient empty." => !empty($_POST['recipient']),
            "Recipient same as sender" => $parsedHexName['name'] != $_SESSION['user']['name'],
            "Incorrect recipient length" => $validationManager->min($_POST['recipient'], 1),
            "Incorrect message length" => $validationManager->min($_POST['message'], 1),
            "User doesn't exist." => $userManager->userExistsHex($parsedHexName['hexid'])
          ];
          $validationManager->validate($tests);
          $receiver = $userManager->selectUserHex($parsedHexName['hexid']);
          $messageManager->sendMessage($_POST['message'], 'message', $_SESSION['user']['user_id'], $receiver['user_id']);
          $this->addMessage("Message sent!");
          $this->refresh();
        } catch (PDOException | ValidationError $e) {
          $this->addMessage(ExceptionHandler::getMessage($e));
          $this->refresh();
        }
      }
      if (isset($_POST['accept-invite'])) {
        $tests = [
          "Maximum number of teams reached" => $validationManager->max($teamMan->returnUserTeamsCount($_SESSION['user']['user_id']), 5),
          "Team already in events" => !$teamMan->teamInEvents($_POST['team-id'])
        ];
        try {
          $validationManager->validate($tests);
          $teamMan->insertTeamParticipation($_SESSION['user']['user_id'], $_POST['team-id']);
          $mesMan->deleteMessageById($_POST['message-id']);
          $this->addMessage("Team joined.");
          $this->log("Team joined. Team: " . $_POST['team_name'], 'team_join');
          $this->redir("profile");
        } catch (PDOException | ValidationError $e) {
          $this->addMessage(ExceptionHandler::getMessage($e));
          $this->refresh();
        }
      }
      if (isset($_POST['decline-invite'])) {
        try {
          $mesMan->deleteMessageById($_POST['message-id']);
          $this->addMessage("Invite declined");
          $this->redir("profile");
        } catch (PDOException $e) {
          $this->addMessage($e);
        }
      }
      if (isset($_POST['mark-message-read'])) {
        try {
          $mesMan->markMessageAsRead($_POST['message-id']);
          $this->redir(substr($_SERVER['REQUEST_URI'], 1));
        } catch (PDOException $e) {
          $this->addMessage($e);
        }
      }
      if (isset($_POST['message-delete'])) {
        try {
          $mesMan->moveMessageToTrash($_POST['message-id']);
          $this->addMessage("Message moved to trash.");
          $this->redir(substr($_SERVER['REQUEST_URI'], 1));
        } catch (PDOException $e) {
          $this->addMessage($e);
        }
      }
      if (isset($_POST['message-delete-complete'])) {
        try {
          $mesMan->deleteMessageById($_POST['message-id']);
          $this->addMessage("Message deleted.");
          $this->redir(substr($_SERVER['REQUEST_URI'], 1));
        } catch (PDOException $e) {
          $this->addMessage($e);
        }
      }
    }
  }
}
