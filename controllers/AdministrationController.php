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

          //Admin validation
          if (!UserManager::authAdmin()) {
              $this->addMessage("Admin rights needed.");
              $this->redir("home");
          }

          //Routing
          if (!empty($params[0]) && $this->isParam($params,0,'getlog')) {
              $logs = $logManager->returnLogs();
              $logIds = array();
              for ($i=0; $i < count($logs); $i++) {
                  $logIds[] = $logs[$i]['log_id'];
              }
              if (!empty($params[1]) && in_array($params[1], $logIds)) {
                  $this->data['log'] = $logManager->returnLogById($params[1]);
                  $this->view = 'logpreview';
              }
          } elseif (!empty($params[0]) && $this->isParam($params,0,'getgameform')) {
              if (in_array($params[1], $gameManager->getGameIds())) {
                  $this->data['gameCur'] = $gameManager->returnGameById($params[1]);
                  $this->addMessage($params[1]);
                  $this->view = 'gameform';
              }
          } else {
              $this->data['logs'] = $logManager->returnLogs();
              $this->data['games'] = $gameManager->returnGames();
              $this->data['events'] = $eventManager->returnEvents();
              $this->header['page_title'] = "Admin Dashboard";
              $this->view = "administration";
          }

          //Handling POST requests
          if ($_POST) {
              if (isset($_POST['event-add'])) {
                  try {
                      if (strlen($_POST['eventName']) > 4) {
                          if (strlen($_POST['eventUrl']) > 5) {
                              $gamePL = $eventManager->getGameLimit($_POST['eventGame']);
                              $eventManager->createEvent($_POST['eventName'], $_POST['eventGame'], $_POST['eventDate'] . " " .  $_POST['eventTime'], $_POST['eventPL'], $gamePL[0], $_POST['eventUrl']);
                              $this->log("Global event has been created", "event_register");
                              $this->addMessage("Global event has been created");
                              $this->redir('events');
                          } else {
                              $this->addMessage("Event url must be atleast 6 characters long.");
                          }
                      } else {
                          $this->addMessage("Event name must be atleast 5 characters long.");
                      }
                  } catch (PDOException $e) {
                      $this->addMessage($e);
                  }
              }
              if (isset($_POST['game-add'])) {
                  try {
                      $gameManager->addGame($_POST['mod-gameadd-name'], $_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit']);
                      $this->log("Game has been added", "game_add");
                      $this->addMessage("Game has been added");
                      $this->redir("administration");
                  } catch (PDOException $e) {
                      $this->addMessage($e);
                  }
              }
              if (isset($_POST['game-edit'])) {
                  try {
                      $ruleset = ($_FILES['file-ruleset']['name'] != "") ? $fileManager->uploadFile($_FILES['file-ruleset'], false, ['pdf'], $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset') : null;
                      $fileIcon = ($_FILES['file-icon']['name'] != "") ? $fileManager->uploadFile($_FILES['file-icon'], true, ['jpg','jpeg','png','bmp','svg'], $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-icon') : null;
                      $fileBackground = ($_FILES['file-background']['name'] != "") ? $fileManager->uploadFile($_FILES['file-background'], true, ['jpg','jpeg','png','bmp','svg'], $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background') : null;

                      $gameManager->editGame($_POST['mod-gameadd-name'], $_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'], $fileBackground, $ruleset, $fileIcon, $_POST['edit-game-id']);
                      $this->log("Game has been edited", "game_edit");
                      $this->addMessage("Game has been edited");
                      $this->redir("administration");
                  } catch (Exception $e) {
                      $this->addMessage($e);
                  }
              }
              if (isset($_POST['event-remove'])) {
                  try {
                      $eventManager->deleteEvent($_POST['remove-event-id']);
                      $this->addMessage("Event deleted");
                      $this->log("Event (".$_POST['remove-event-id'].") removed.", "event_drop");
                      $this->redir("administration");
                  } catch (PDOException $e) {
                      $this->addMessage($e);
                  }
              }
              if (isset($_POST['bracket-remove'])) {
                  try {
                      $matchesManager->dropMatches($_POST['remove-event-id']);
                      $this->addMessage("Bracket deleted");
                      $this->log("Bracket of event (".$_POST['remove-event-id'].") removed.", "bracket_drop");
                      $this->redir("administration");
                  } catch (PDOException $e) {
                      $this->addMessage($e);
                  }
              }
              if (isset($_POST['game-remove'])) {
                  try {
                      $gameManager->deleteGame($_POST['remove-game-id']);
                      $this->addMessage("Game deleted");
                      $this->log("Game (".$_POST['remove-game-id'].") removed.", "game_drop");
                      $this->redir("administration");
                  } catch (PDOException $e) {
                      $this->addMessage($e);
                  }
              }
              if (isset($_POST['user-verification'])) {
                  try {
                      if (isset($_POST['verification_username'])) {
                          if (strlen($_POST['verification_username']) > 0) {
                              if (preg_match("/^[a-zA-Z0-9]+#[a-fA-F0-9]{4}$/", $_POST['verification_username']) || preg_match("/^#[a-fA-F0-9]{4}$/", $_POST['verification_username'])) {
                                  $fullName = $userManager->parseHexname($_POST['verification_username']);
                                  try {
                                      $userManager->verifyUser($fullName['hexid']);
                                  } catch (PDOException $e) {
                                      $this->addMessage($e);
                                  }
                                  $this->addMessage("User verified");
                                  $this->log("User verified"."//".$_POST['verification_username'], "user_verify");
                                  $this->redir("administration");
                              }
                          } else {
                              $this->addMessage("Field empty");
                          }
                      }
                      if (isset($_POST['verification_teamname'])) {
                          if (strlen($_POST['verification_teamname']) > 0) {
                              $fullName = $userManager->parseHexname($_POST['verification_teamname']);
                              try {
                                  $teamManager->verifyTeam($fullName['hexid']);
                              } catch (PDOException $e) {
                                  $this->addMessage($e);
                              }
                              $this->addMessage("Team verified");
                              $this->log("Team verify"."//".$_POST['verification_teamname'], "team_verify");
                              $this->redir("administration");
                          } else {
                              $this->addMessage("Field empty");
                          }
                      }
                  } catch (PDOException $e) {
                      $this->addMessage($e);
                  }
              }
              if (isset($_POST['user-admin-tool'])) {
                  if (strlen($_POST['user-to-admin']) > 0) {
                      if (preg_match("/^[a-zA-Z0-9]+#[a-fA-F0-9]{4}$/", $_POST['user-to-admin']) || preg_match("/^#[a-fA-F0-9]{4}$/", $_POST['user-to-admin'])) {
                          $fullName = $userManager->parseHexname($_POST['user-to-admin']);
                          try {
                              $userManager->adminUser($fullName['hexid'], $_POST['admin-type']);
                          } catch (PDOException $e) {
                              $this->addMessage($e);
                          }
                          $this->addMessage("User given admin rights");
                          $this->log("User given admin rights"."//".$_POST['user-to-admin'], "user_admin");
                          $this->redir("administration");
                      }
                  } else {
                      $this->addMessage("Field empty");
                  }
              }
          }
      }
  }
