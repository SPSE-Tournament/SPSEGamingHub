<?php
  class ProfileController extends Controller
  {
      public function parse($params)
      {
          $userMan = new UserManager();
          $mesMan = new MessageManager();
          $teamMan = new TeamManager();
          $gameMan = new GameManager();
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
              } elseif ($params[0] == 'getusers') {
                  if (!empty($params[1])) {
                      $str = $params[1];
                  } else {
                      $str = "";
                  }
                  $response = $userMan->liveSearchUsers($str);
                  $this->data['response'] = $response;
                  $this->view = 'userlist';
              } elseif ($params[0] == 'getteamhint') {
                  if (!empty($params[1])) {
                      $str = $params[1];
                  } else {
                      $str = "";
                  }
                  $response = $teamMan->liveSearchTeams($str);
                  ;
                  $this->data['response'] = $response;
                  $this->view = "teamlist";
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
              if (isset($_POST['team-add'])) {
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
                                  if (!in_array($parsedHexName['name'], $realUsers['names']) && !in_array(mb_strtoupper($parsedHexName['hexid']), $realUsers['hexids'])) {
                                      if ($userMan->userExistsHex($parsedHexName['hexid'])) {
                                          $receiverId = $userMan->selectUserHex($parsedHexName['hexid']);
                                          $inviteMessage = 'You have been invited to join a team: <span style="color:orange;">' . $_POST['team-name'] . '</span> in a game: <span style="color:orange;">' . $_POST['team-game'] . '</span>';
                                          $mesMan->sendMessage(
                                              $inviteMessage,
                                              'invite',
                                              $_SESSION['user']['user_id'],
                                              $receiverId['user_id'],
                                              $_POST['team-id']
                                          );
                                          $this->addMessage("Your invite has been sent.");
                                          $this->log("Invite sent. Sender: ".$_SESSION['user']['name']."#".$_SESSION['user']['user_hexid'].', Receiver: '. $_POST['user-to-invite'] . ", Message: ". $_POST['message'], 'message_sent');
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
                      $this->log("Team (".$_POST['team-id'].") removed", "team_removal");
                      $this->redir("profile");
                  } catch (PDOException $e) {
                      $this->addMessage($e);
                  }
              }
              if (isset($_POST['team-leave'])) {
                  try {
                      $teamMan->leaveTeam($_POST['team-id'], $_SESSION['user']['user_id']);
                      $this->addMessage("Left the team.");
                      $this->log("Team (".$_POST['team-id'].") left", "team_leave");
                      $this->redir("profile");
                  } catch (Exception $e) {
                      $this->addMessage($e);
                  }
              }
          }
      }
  }
