<?php
  class MessagesController extends Controller
  {

      public function parse($params)
      {
      $messageManager = new MessageManager();
      $userManager = new UserManager();
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
              $parsedHexName = $userManager->parseHexname($_POST['recipient']);
              $tests = [
                "Recipient same as sender" => $parsedHexName['name'] != $_SESSION['user']['name'],
                "Message empty." =>!empty($_POST['message']),
                "Recipient empty." =>!empty($_POST['recipient']),
                "Incorrect recipient length" =>(bool)(strlen($_POST['recipient']) > 1),
                "Incorrect message length" =>(bool)(strlen($_POST['message']) > 0),
                "User doesn't exist." => $userManager->userExistsHex($parsedHexName['hexid'])
              ];
                foreach ($tests as $key => $value) {
                  if (!$value) {
                    $this->addMessage($key);
                    $this->redir("messages");
                  }
                }
                try {
                  $receiver = $userManager->selectUserHex($parsedHexName['hexid']);
                  $messageManager->sendMessage($_POST['message'], 'message', $_SESSION['user']['user_id'], $receiver['user_id']);
                  $this->addMessage("Message sent!");
                  $this->redir("messages");
                } catch (PDOException $e) {
                  $this->addMessage($e->getMessage());
                }
            }
              if (isset($_POST['accept-invite'])) {
                  try {
                      if ($teamMan->returnUserTeamsCount($_SESSION['user']['user_id']) < 5) {
                          if (!$teamMan->teamInEvents($_POST['team-id'])) {
                              $teamMan->insertTeamParticipation($_SESSION['user']['user_id'], $_POST['team-id']);
                              $mesMan->deleteMessageById($_POST['message-id']);
                              $this->addMessage("Team joined.");
                              $this->log("Team joined. Team: " . $_POST['team_name'], 'team_join');
                              $this->redir("profile");
                          } else {
                              $this->addMessage("Team already in events.");
                              $this->redir("profile");
                          }
                      } else {
                          $this->addMessage("Maximum number of teams reached.");
                          $this->redir("profile");
                      }
                  } catch (PDOException $e) {
                      $this->addMessage($e);
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
