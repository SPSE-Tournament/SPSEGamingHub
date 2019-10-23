<?php
  class ProfileController extends Controller {
      public function parse($params) {
        $userMan = new UserManager();
        $mesMan = new MessageManager();
        $teamMan = new TeamManager();
        $gameMan = new GameManager();
        $messageTypes = array('message','invite','trash');

        if(isset($_POST['team-add'])) {
          try {
            $teamMan->insertTeam($_POST['teamName'], $_SESSION['user']['user_id'], $_POST['teamGame']);
            $teamMan->insertTeamParticipation($_SESSION['user']['user_id'], Db::getLastId());
            $this->addMessage("Your team has been created.");
            $this->log("Team has been created", "team_creation");
            $this->redir("profile");
          } catch (PDOException $e) {
            $this->addMessage($e);
          }
        }

        if (!empty($params[0])) {

          if ($params[0] == 'logout') {

              $this->log("User logout.", "login");
              $userMan->logout();
              $this->addMessage("Byl jste úspěšně odhlášen.");
              $this->redir("login");

          } if ($params[0] == 'messages' && !empty($params[1]) && in_array($params[1], $messageTypes)) {
            $messages = $mesMan->returnMessagesByType($_SESSION['user']['user_id'], $params[1]);
            $this->data['messages'] = $messages;
            $this->data['mesDump'] = var_dump($messages);
            $this->data['date'] = new DateTime("now");
            $this->view = 'messages';
          }
          else if($params[0] == 'getmessage') {
            $messages = $mesMan->returnMessages($_SESSION['user']['user_id']);
            $messageIds = array();
            for ($i=0; $i < count($messages); $i++) {
                  $messageIds[] = $messages[$i]['message_id'];
            }
            if (!empty($params[1]) && in_array($params[1], $messageIds)) {
              $this->data['message'] = $mesMan->returnMessageById($params[1]);
              $this->view = 'message';
            }

          }
          else if ($params[0] == 'getusers') {
            if (!empty($params[1])) {
              $str = $params[1];
            } else {
              $str = "";
            }
            $response = $userMan->liveSearchUsers($str);
            $this->data['response'] = $response;
            $this->view = 'userlist';
          }
        } else {
          $hasTeams = ($teamMan->returnUserTeamsCount($_SESSION['user']['user_id']) > 0 ? true : false);
          $games = $gameMan->returnGames();
          $this->data['user'] = $_SESSION['user'];
          $this->data['userTeams'] = $teamMan->returnUserTeams($_SESSION['user']['user_id']);
          $this->data['hasTeams'] = $hasTeams;
          $this->data['games'] = $games;
          $this->view = 'profile';
        }
      }
  }
?>
