<?php
  class ProfileController extends Controller {
      public function parse($params) {
        $userMan = new UserManager();
        $mesMan = new MessageManager();
        $teamMan = new TeamManager();
        $gameMan = new GameManager();
        $date = new DateTime("now");
        $messageTypes = array('message','invite','trash');
        if (!$_SESSION['logged']) {
          $this->redir("login");
        }
        //Routing
        if (!empty($params[0])) {
          if ($params[0] == 'logout') {
              $this->log("User logout.", "login");
              $userMan->logout();
              $this->addMessage("Byl jste úspěšně odhlášen.");
              $this->redir("login");

          } if ($params[0] == 'messages' && !empty($params[1]) && in_array($params[1], $messageTypes)) {

            if (empty($params[2]))
              $messages = $mesMan->returnMessagesByType($_SESSION['user']['user_id'], $params[1], 0,5);
             else if (!empty($params[2]))
              $messages = $mesMan->returnMessagesByType($_SESSION['user']['user_id'], $params[1], (int)$params[2]*5-5,(int)$params[2]*5);

            if (ceil((int)$mesMan->returnMessagesByTypeCount($_SESSION['user']['user_id'],$params[1])/5) > 1)
              $this->data['numOfPages'] = ceil((int)$mesMan->returnMessagesByTypeCount($_SESSION['user']['user_id'],$params[1])/5);
            else
              $this->data['numOfPages'] = 0;

            $this->data['messages'] = $messages;
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
          } else if ($params[0] == 'getteamhint') {
            if (!empty($params[1])) {
              $str = $params[1];
            } else {
              $str = "";
            }
            $response = $teamMan->liveSearchTeams($str);;
            $this->data['response'] = $response;
            $this->view = "teamlist";
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
          $this->header['page_desc'] = "SPSE Gaming Hub - Profile page";
          $this->view = 'profile';
        }

        //Handling POST
        if ($_POST) {
          if(isset($_POST['team-add'])) {
            try {
              if (strlen($_POST['teamName']) > 3) {
                if (!$teamMan->teamExists($_POST['teamName'])) {
                  if (preg_match("/^[a-zA-Z][a-zA-Z0-9 ]{3,29}$/", $_POST['teamName'])) {
                    $teamMan->insertTeam(preg_replace('!\s+!', ' ', $_POST['teamName']), $_SESSION['user']['user_id'], $_POST['teamGame']);
                    $teamMan->insertTeamParticipation($_SESSION['user']['user_id'], Db::getLastId());
                    $this->addMessage("Your team has been created.");
                    $this->log("Team has been created", "team_creation");
                    $this->redir("profile");
                  } else {
                    $this->addMessage("Team name in a wrong format.");
                    $this->redir("profile");
                  }
                } else {
                  $this->addMessage("Team name taken.");
                  $this->redir("profile");
                }
              } else {
                $this->addMessage("Team name must be atleast 4 characters long.");
                $this->redir("profile");
              }
            } catch (PDOException $e) {
              $this->addMessage($e);
            }
          }
          if (isset($_POST['user-invite'])) {
            try {
              if (strlen($_POST['user-to-invite']) > 0) {
                $parsedHexName = $userMan->parseHexname($_POST['user-to-invite']);
                if ($parsedHexName['name'] != $_SESSION['user']['name']) {
                  if (preg_match("/^[a-zA-Z0-9]+#[a-fA-F0-9]{4}$/", $_POST['user-to-invite']) || preg_match("/^#[a-fA-F0-9]{4}$/", $_POST['user-to-invite'])) {
                    $realUsers = $teamMan->formatUsersInATeam($teamMan->returnUsersInATeam($_POST['team-id']));
                    if (!in_array($parsedHexName['name'],$realUsers['names']) && !in_array(mb_strtoupper($parsedHexName['hexid']),$realUsers['hexids'])) {
                      if ($userMan->userExistsHex($parsedHexName['hexid'])) {
                        $receiverId = $userMan->selectUserHex($parsedHexName['hexid']);
                        $inviteMessage = 'You have been invited to join a team: <span style="color:orange;">' . $_POST['team-name'] . '</span> in a game: <span style="color:orange;">' . $_POST['team-game'] . '</span>';
                        $mesMan->sendMessage($inviteMessage,'invite',
                        $date->format('Y-m-d H:i:s'),
                        $_SESSION['user']['user_id'],$_SESSION['user']['name'],$receiverId['user_id'],$parsedHexName['name'], $_POST['team-id']);
                        $this->addMessage("Your invite has been sent.");
                        $this->log("Invite sent. Sender: ".$_SESSION['user']['name']."#".$_SESSION['user']['user_hexid'].', Receiver: '. $_POST['user-to-invite'] . ", Message: ". $_POST['message'] ,'message_sent');
                        $this->redir("profile");
                      } else {
                        $this->addMessage("User doesn't exist");
                        $this->redir("profile");
                    }
                    } else {
                      $this->addMessage("User already in the team, are you actually blind?");
                      $this->redir("profile");
                    }
                  } else {
                    $this->addMessage("User format invalid");
                    $this->redir("profile");
                  }
                } else {
                  $this->addMessage("Can't invite yourself, can you mate?");
                  $this->redir("profile");
                }
              } else {
                $this->addMessage("Come on now, you have to type something.");
                $this->redir("profile");
              }

            } catch (PDOException $e) {
              $this->addMessage($e);
            }
        }
        if (isset($_POST['team-removal'])) {
          try {
            $teamMan->removeTeam($_POST['team-id']);
            $this->addMessage("Team removed.");
            $this->log("Team (".$_POST['team-id'].") removed","team_removal");
            $this->redir("profile");
          } catch (PDOException $e) {
            $this->addMessage($e);
          }
        }
        if (isset($_POST['team-leave'])) {
          try {
              $teamMan->leaveTeam($_POST['team-id'], $_SESSION['user']['user_id']);
              $this->addMessage("Left the team.");
              $this->log("Team (".$_POST['team-id'].") left","team_leave");
              $this->redir("profile");
          } catch (Exception $e) {
            $this->addMessage($e);
          }
        }
        }

      }
  }
?>
