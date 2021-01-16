<?php
class ProfileController extends Controller
{
    public function parse($params)
    {
        $userMan = new UserManager();
        $mesMan = new MessageManager();
        $teamMan = new TeamManager();
        $gameMan = new GameManager();
        $validationManager = new ValidationManager();
        $messageTypes = array('message', 'invite', 'trash');
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
                $response = $teamMan->liveSearchTeams($str);;
                $this->data['response'] = $response;
                $this->view = "teamlist";
            }
        } else {
            $hasTeams = ($teamMan->returnUserTeamsCount($_SESSION['user']['user_id']) > 0);
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
                    $tests = [
                        "Team name must be at least 4 characters long" => $validationManager->min($_POST['teamName'], 4),
                        "Team name taken" => !$teamMan->teamExists($_POST['teamName']),
                        "Team name contains forbidden characters" => $validationManager->teamname($_POST['teamName']),
                    ];
                    $validationManager->validate($tests);
                    $teamMan->insertTeam(preg_replace('!\s+!', ' ', $_POST['teamName']), $_SESSION['user']['user_id'], $_POST['teamGame']);
                    $teamMan->insertTeamParticipation($_SESSION['user']['user_id'], Db::getLastId());
                    $this->addMessage("Your team has been created.");
                    $this->log("Team has been created", "team_creation");
                    $this->redir("profile");
                } catch (ValidationError | PDOException $e) {
                    $this->addMessage(ExceptionHandler::getMessage($e));
                    $this->refresh();
                }
            }
            if (isset($_POST['user-invite'])) {
                try {
                    $parsedHexName = $userMan->parseHexname($_POST['user-to-invite']);
                    $realUsers = $teamMan->formatUsersInATeam($teamMan->returnUsersInATeam($_POST['team-id']));
                    $tests = [
                        "Fields empty" => $validationManager->notEmpty($_POST['user-to-invite']),
                        "Receiver same as sender" => $parsedHexName['name'] != $_SESSION['user']['name'],
                        "Field contains forbidden characters" => $validationManager->hexname($_POST['user-to-invite'])
                            || $validationManager->hexid($_POST['user-to-invite']),
                        "User already in the team" => !in_array($parsedHexName['name'], $realUsers['names']) && !in_array(mb_strtoupper($parsedHexName['hexid']), $realUsers['hexids']),
                        "User doesn't exist" => $userMan->userExistsHex($parsedHexName['hexid'])
                    ];
                    $validationManager->validate($tests);
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
                    $this->log("Invite sent. Sender: " . $_SESSION['user']['name'] . "#" . $_SESSION['user']['user_hexid'] . ', Receiver: ' . $_POST['user-to-invite'] . ", Message: " . $_POST['message'], 'message_sent');
                } catch (ValidationError | PDOException $e) {
                    $this->addMessage(ExceptionHandler::getMessage($e));
                }
                $this->refresh();
            }
            if (isset($_POST['team-removal'])) {
                try {
                    $teamMan->removeTeam($_POST['team-id']);
                    $this->addMessage("Team removed.");
                    $this->log("Team (" . $_POST['team-id'] . ") removed", "team_removal");
                    $this->redir("profile");
                } catch (PDOException $e) {
                    $this->addMessage($e);
                }
            }
            if (isset($_POST['team-leave'])) {
                try {
                    $teamMan->leaveTeam($_POST['team-id'], $_SESSION['user']['user_id']);
                    $this->addMessage("Left the team.");
                    $this->log("Team (" . $_POST['team-id'] . ") left", "team_leave");
                    $this->redir("profile");
                } catch (Exception $e) {
                    $this->addMessage($e);
                }
            }
        }
    }
}
