<?php
  class ProfileController extends Controller {
      public function parse($params) {
        $userMan = new UserManager();
        $mesMan = new MessageManager();
        $teamMan = new TeamManager();
        $gameMan = new GameManager();
        $date = new DateTime("now");
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
        if (isset($_POST['user-invite'])) {
          try {
            $parsedHexName = $userMan->parseHexname($_POST['user-to-invite']);
            $receiverId = $userMan->selectUser($parsedHexName['name']);
            $inviteMessage = 'You have been invited to join a team: <span style="color:orange;">' . $_POST['team-name'] . '</span> in a game: <span style="color:orange;">' . $_POST['team-game'] . '</span>';
            $mesMan->sendMessage($inviteMessage,'invite',
             $date->format('Y-m-d H:i:s'),
             $_SESSION['user']['user_id'],$_SESSION['user']['name'],$receiverId['user_id'],$parsedHexName['name'], $_POST['team-id']);
            $this->addMessage("Your invite has been sent.");
            $this->log("Invite sent. Sender: ".$_SESSION['user']['name']."#".$_SESSION['user']['user_hexid'].', Receiver: '. $_POST['user-to-invite'] . ", Message: ". $_POST['message'] ,'message_sent');
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
          else if ($params[0] == 'getteam' && !empty($params[1]) && $teamMan->teamExists($params[1])) {
              $this->data['team'] = $teamMan->returnTeamById($params[1]);
              $this->data['users'] = $teamMan->returnUsersInATeam($params[1]);
              $this->view = 'team';
          }
        } else {
          $hasTeams = ($teamMan->returnUserTeamsCount($_SESSION['user']['user_id']) > 0 ? true : false);
          $games = $gameMan->returnGames();
          $this->data['user'] = $_SESSION['user'];
          $this->data['userTeams'] = $teamMan->returnUserTeamsWithPlayers($_SESSION['user']['user_id']);
          $this->data['hasTeams'] = $hasTeams;
          $this->data['games'] = $games;
          $this->header['page_title'] = "Profile";
          $this->view = 'profile';
        }
      }
  }
?>
