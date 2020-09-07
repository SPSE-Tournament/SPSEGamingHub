<?php
    class RouterController extends Controller {

      protected $controller;

        public function parse($params){
          $userMan = new UserManager();
          $mesMan = new MessageManager();
          $date = new DateTime();
          $teamMan = new TeamManager();
          $parsedU = $this->parseURL($params[0]);
          $this->header['page_keywords'] = "SPSE Gaming, SPSE Esport, SPSE Gaming Events, SPSE Esport Events, SPŠE Esport, SPŠE Gaming, SPŠE Gaming Events, SPŠE Esport Events,";
          if (empty($parsedU[0])) {
            $this->redir('home');
          }

          $controllerClass = $this->toCamelCase(array_shift($parsedU)) . 'Controller';
          if (file_exists('controllers/'.$controllerClass . '.php')) {
            $this->controller = new $controllerClass;
          } else {
            $this->redir('error');
          }

          $this->controller->parse($parsedU);

          if (isset($_POST)) {
            if (isset($_POST['message-add'])) {
              try {
                $parsedHexName = $userMan->parseHexname($_POST['receiver']);
                if ($userMan->userExistsHex($parsedHexName['hexid'])) {
                  $receiverId = $userMan->selectUserHex($parsedHexName['hexid']);
                  $mesMan->sendMessage($_POST['message'],'message', $date->format('Y-m-d H:i:s'),$_SESSION['user']['user_id'],$_SESSION['user']['name'],$receiverId['user_id'],$parsedHexName['name']);
                  $this->addMessage("Your message has been sent.");
                  $this->log("Message sent. Sender: ".$_SESSION['user']['name']."#".$_SESSION['user']['user_hexid'].', Receiver: '. $_POST['receiver'] . ", Message: ". $_POST['message'] ,'message_sent');
                  $this->redir("profile");
                } else {
                  $this->addMessage("User doesn't exist.");
                }


              } catch (PDOException $e) {
                $this->addMessage($e);
              }
          }
          if(isset($_POST['accept-invite'])) {
            try {
              if ($teamMan->returnUserTeamsCount($_SESSION['user']['user_id']) < 5) {
                if (!$teamMan->teamInEvents($_POST['team-id'])) {
                  $teamMan->insertTeamParticipation($_SESSION['user']['user_id'], $_POST['team-id']);
                  $mesMan->deleteMessageById($_POST['message-id']);
                  $this->addMessage("Team joined.");
                  $this->log("Team joined. Team: " . $_POST['team_name'],'team_join');
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
          if(isset($_POST['decline-invite'])) {
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
                $this->redir(substr($_SERVER['REQUEST_URI'],1));
            } catch (PDOException $e) {
              $this->addMessage($e);
            }
          }
          if (isset($_POST['message-delete'])) {
            try {
                $mesMan->moveMessageToTrash($_POST['message-id']);
                $this->addMessage("Message moved to trash.");
                $this->redir(substr($_SERVER['REQUEST_URI'],1));
            } catch (PDOException $e) {
              $this->addMessage($e);
            }
          }
          if (isset($_POST['message-delete-complete'])) {
            try {
                $mesMan->deleteMessageById($_POST['message-id']);
                $this->addMessage("Message deleted.");
                $this->redir(substr($_SERVER['REQUEST_URI'],1));
            } catch (PDOException $e) {
              $this->addMessage($e);
            }
          }
          }


          if ($this->checkLogged()) {
            $this->data['usrname'] = $_SESSION['user']['name'];
          }
          $this->checkAdmin();
          $this->data['title'] = $this->controller->header['page_title'];
          $this->data['desc'] = $this->controller->header['page_desc'];
          $this->data['keywords'] = $this->controller->header['page_keywords'];
          $this->data['messages'] = $this->returnMessages();
          $this->data['logged'] = $_SESSION['logged'];
          if (isset($_SESSION['user'])) {
            $this->data['user'] = $_SESSION['user'];

          }
            $this->header['page_title'] = "SPSE Gaming Hub.";
            if (substr($params[0],0,4) == "/api") {
              $this->view = $this->controller->showView();
            } else {
              $this->view = "layout";
            }

        }

        private function parseURL($url) {
          $prsU = parse_url($url);
          $prsU['path'] = trim(ltrim($prsU['path'], "/"));
          $divPath = explode("/", $prsU['path']);
          return $divPath;
        }

        private function toCamelCase($txt) {
          $sent = str_replace('-', ' ', $txt);
          $sent = ucwords($sent);
          $sent = str_replace(' ', '', $sent);
          return $sent;
        }








    }







 ?>
