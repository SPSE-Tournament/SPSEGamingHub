<?php
class AdministrationController extends Controller
{
    public function parse($params)
    {
        $gameManager = new GameManager();
        $eventManager = new EventManager();
        $logManager = new LogManager();
        $fileManager = new FileManager();
        $stringManager = new StringManager();
        $userManager = new UserManager();
        $teamManager = new TeamManager();
        $matchesManager = new MatchesManager();
        $validationManager = new ValidationManager();

        //Admin validation
        if (!UserManager::authAdmin()) {
            $this->addMessage("Admin rights needed.");
            $this->redir("home");
        }

        $this->data['logs'] = $logManager->returnLogs();
        $this->data['games'] = $gameManager->returnGames();
        $this->data['events'] = $eventManager->returnEvents();
        $this->header['page_title'] = "Admin Dashboard";
        $this->view = "administration";


        //Handling POST requests
        if ($_POST) {
            if (isset($_POST['event-add'])) {
                try {
                    $tests = [
                        "Event name must be at least 5 characters long" => $validationManager->min($_POST['eventName'], 5),
                        "Event url must be at least 6 characters long" => $validationManager->min($_POST['eventUrl'], 6)
                    ];
                    $validationManager->validate($tests);
                    $gamePL = $eventManager->getGameLimit($_POST['eventGame']);
                    $eventManager->createEvent($_POST['eventName'], $_POST['eventGame'], $_POST['eventDate'] . " " .  $_POST['eventTime'], $_POST['eventPL'], $gamePL[0], $_POST['eventUrl']);
                    $this->log("Global event has been created", "event_register");
                    $this->addMessage("Global event has been created");
                    $this->redir('events');
                } catch (ValidationError | PDOException $e) {
                    $this->addMessage(ExceptionHandler::getMessage($e));
                    $this->refresh();
                }
            }
            if (isset($_POST['game-add'])) {
                try {
                    $gameManager->addGame($_POST['mod-gameadd-name'], $_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit']);
                    $this->log("Game has been added", "game_add");
                    $this->addMessage("Game has been added");
                    $this->refresh();
                } catch (PDOException $e) {
                    $this->addMessage($e);
                }
            }
            if (isset($_POST['game-edit'])) {
                try {
                    $ruleset = ($_FILES['file-ruleset']['name'] != "") 
                        ? $fileManager->uploadFile($_FILES['file-ruleset'], false, ['pdf'], $stringManager->stripSpaces($_POST['mod-gameadd-shortname']) . '-ruleset') 
                        : null;
                    $fileIcon = ($_FILES['file-icon']['name'] != "") 
                        ? $fileManager->uploadFile($_FILES['file-icon'], true, ['jpg', 'jpeg', 'png', 'bmp', 'svg'], $stringManager->stripSpaces($_POST['mod-gameadd-shortname']) . '-icon') 
                        : null;
                    $fileBackground = ($_FILES['file-background']['name'] != "") 
                        ? $fileManager->uploadFile($_FILES['file-background'], true, ['jpg', 'jpeg', 'png', 'bmp', 'svg'], $stringManager->stripSpaces($_POST['mod-gameadd-shortname']) . '-background') 
                        : null;
                    $gameManager->editGame($_POST['mod-gameadd-name'], $_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'], $fileBackground, $ruleset, $fileIcon, $_POST['edit-game-id']);
                    $this->log("Game has been edited", "game_edit");
                    $this->addMessage("Game has been edited");
                    $this->refresh();
                } catch (Exception $e) {
                    $this->addMessage($e);
                }
            }
            if (isset($_POST['event-remove'])) {
                try {
                    $eventManager->deleteEvent($_POST['remove-event-id']);
                    $this->addMessage("Event deleted");
                    $this->log("Event (" . $_POST['remove-event-id'] . ") removed.", "event_drop");
                    $this->refresh();
                } catch (PDOException $e) {
                    $this->addMessage($e);
                }
            }
            if (isset($_POST['bracket-remove'])) {
                try {
                    $matchesManager->dropMatches($_POST['remove-event-id']);
                    $this->addMessage("Bracket deleted");
                    $this->log("Bracket of event (" . $_POST['remove-event-id'] . ") removed.", "bracket_drop");
                    $this->refresh();
                } catch (PDOException $e) {
                    $this->addMessage($e);
                }
            }
            if (isset($_POST['game-remove'])) {
                try {
                    $gameManager->deleteGame($_POST['remove-game-id']);
                    $this->addMessage("Game deleted");
                    $this->log("Game (" . $_POST['remove-game-id'] . ") removed.", "game_drop");
                    $this->refresh();
                } catch (PDOException $e) {
                    $this->addMessage($e);
                }
            }
            if (isset($_POST['user-verification'])) {

                if (isset($_POST['verification_username'])) {
                    try {
                        $tests = [
                            "Username empty" => $validationManager->notEmpty($_POST['verification_username']),
                            "Username contains forbidden characters" => $validationManager->hexname($_POST['verification_username']) ||
                                $validationManager->hexid($_POST['verification_username'])
                        ];
                        $validationManager->validate($tests);
                        $fullName = $userManager->parseHexname($_POST['verification_username']);
                        $validationManager->validate($tests);
                        $userManager->verifyUser($fullName['hexid']);
                        $this->addMessage("User verified");
                        $this->log("User verified" . "//" . $_POST['verification_username'], "user_verify");
                    } catch (ValidationError | PDOException $e) {
                        $this->addMessage(ExceptionHandler::getMessage($e));
                    }
                    $this->refresh();
                } elseif (isset($_POST['verification_teamname'])) {
                    try {
                        $tests = [
                            "Teamname empty" => $validationManager->notEmpty($_POST['verification_teamname']),
                        ];
                        $validationManager->validate($tests);
                        $team = $teamManager->returnTeamByName($_POST['verification_teamname']);
                        $teamManager->verifyTeam($team['team_id']);
                        $this->addMessage("Team verified");
                        $this->log("Team verify" . "//" . $_POST['verification_teamname'], "team_verify");
                    } catch (ValidationError | PDOException $e) {
                        $this->addMessage(ExceptionHandler::getMessage($e));
                    }
                    $this->refresh();
                }
            }
            if (isset($_POST['user-admin-tool'])) {
                try {
                    $tests = [
                        "Fields empty" => $validationManager->notEmpty($_POST['user-to-admin']),
                        "Fields contain forbidden characters" => $validationManager->hexnameOrhexid($_POST['user-to-admin'])
                    ];
                    $fullName = $userManager->parseHexname($_POST['user-to-admin']);
                    $userManager->adminUser($fullName['hexid'], $_POST['admin-type']);
                    $this->addMessage("User given admin rights");
                    $this->log("User given admin rights" . "//" . $_POST['user-to-admin'], "user_admin");
                } catch (ValidationError | PDOException $e) {
                    $this->addMessage(ExceptionHandler::getMessage($e));
                }
                $this->refresh();
            }
            if (isset($_POST['password-change'])) {
                try {
                    $tests = [
                        "Password must contain at least 4 characters" => $validationManager->min($_POST['pw'], 4),
                        "Username in wrong format (Username#hexid)" => $validationManager->hexnameOrhexid($_POST['user'])
                    ];
                    $validationManager->validate($tests);
                    $fullName = $userManager->parseHexname($_POST['user']);
                    $userManager->changePassword($fullName['hexid'], $_POST['pw']);
                    $this->addMessage("Password changed");
                    $this->log("User password changed" . "//" . $_POST['user'], "user_admin");
                    $this->refresh();
                } catch (ValidationError | PDOException $e) {
                    $this->addMessage(ExceptionHandler::getMessage($e));
                }
                $this->refresh();
            }
            if (isset($_POST['data-filler'])) {
                try {
                    //$tests = [
                     //   "That number of teams can't fit in the tournament" => $validationManager->min($_POST['pw'], 4)
                    //];
                    //$validationManager->validate($tests);
                    $event = $eventManager->returnEventById($_POST['event-id']);
                    $game = $gameManager->returnGameById($event['game_id']); 
                    for ($i = 0; $i < intval($_POST['number-of-teams']); $i++) {
                        $captain = $userManager->registerDummyUser();
                        $teamName = bin2hex(random_bytes(6));
                        $teamManager->insertTeam($teamName, $captain, $event['game_id']);
                        $teamId = Db::getLastId();
                        $teamManager->insertTeamParticipation(
                            $captain, $teamId
                        );
                        $eventManager->insertEventParticipation(
                            $captain, $event['event_id'], $teamId
                        );
                    }
                    $this->addMessage("Data filled");
                    $this->refresh();
                } catch (ValidationError | PDOException $e) {
                    $this->addMessage(ExceptionHandler::getMessage($e));
                }
            
            }
        }
    }
}
