<?php
  class AdministrationController extends Controller {
    public function parse($params) {
      $gameManager = new GameManager();
      $eventManager = new EventManager();
      $logManager = new LogManager();
      $fileManager = new FileManager();
      $stringManager = new StringManager();
      $userManager = new UserManager();
      $teamManager = new TeamManager();

      //Admin validation
      if (!UserManager::authAdmin()) {
        $this->addMessage("Admin rights needed.");
        $this->redir("home");
      }

      //Routing
      if (!empty($params[0]) && $params[0] == 'getlog') {
        $logs = $logManager->returnLogs();
        $logIds = array();
        for ($i=0; $i < count($logs); $i++) {
              $logIds[] = $logs[$i]['log_id'];
        }
        if (!empty($params[1]) && in_array($params[1], $logIds)) {
          $this->data['log'] = $logManager->returnLogById($params[1]);
          $this->view = 'logpreview';
          }
      } else if (!empty($params[0]) && $params[0] == 'getgameform') {
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
              if (!isset($_FILES['file-ruleset']) && !isset($_FILES['file-background'])) {
                  $gameManager->addGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit']);
              } else if (isset($_FILES['file-background']) && !isset($_FILES['file-ruleset'])) {
                  $fu = $fileManager->uploadFile($_FILES['file-background'], true, array('jpg','jpeg','png','bmp'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background');
                  $gameManager->addGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'], $fu);
                  $this->addMessage($res);
              } else if (!isset($_FILES['file-background']) && isset($_FILES['file-ruleset'])) {
                  $fu = $fileManager->uploadFile($_FILES['file-background'], false, array('pdf'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset');
                  $gameManager->addGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],$fu);
              } else if (isset($_FILES['file-background']) && isset($_FILES['file-ruleset'])) {
                  $fu = $fileManager->uploadFile($_FILES['file-background'], true, array('jpg','jpeg','png','bmp'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background');
                  $fu2 = $fileManager->uploadFile($_FILES['file-background'], false, array('pdf'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset');
                  $gameManager->addGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],$fu,$fu2);
             }
              $this->log("Game has been added","game_add");
              $this->addMessage("Game has been added");
              $this->redir("administration");
            } catch (PDOException $e) {
              $this->addMessage($e);
            }
        }
        if (isset($_POST['game-edit'])) {
            try {
              if ($_FILES['file-ruleset']['name'] == "" && $_FILES['file-background']['name'] == "" && $_FILES['file-icon']['name'] == "") {
                $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'], null, null,null, $_POST['edit-game-id']);

              } else if ($_FILES['file-ruleset']['name'] == "" && $_FILES['file-background']['name'] != "" && $_FILES['file-icon']['name'] == "") {
                $fu = $fileManager->uploadFile($_FILES['file-background'], true, array('jpg','jpeg','png','bmp','svg'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background');
                $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'], $fu,null,null, $_POST['edit-game-id']);
                $this->addMessage($res);

              } else if ($_FILES['file-ruleset']['name'] != "" && $_FILES['file-background']['name'] == "" && $_FILES['file-icon']['name'] == "") {
                $fu = $fileManager->uploadFile($_FILES['file-ruleset'], false, array('pdf'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset');
                $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],null,$fu,null, $_POST['edit-game-id']);

              } else if ($_FILES['file-ruleset']['name'] == "" && $_FILES['file-background']['name'] == "" && $_FILES['file-icon']['name'] != "") {
                $fu = $fileManager->uploadFile($_FILES['file-icon'], true, array('jpg','jpeg','png','bmp','svg'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-icon');
                $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],null,null,$fu, $_POST['edit-game-id']);
              }
               else if ($_FILES['file-ruleset']['name'] != "" && $_FILES['file-background']['name'] != "" && $_FILES['file-icon']['name'] == "") {
               $fu = $fileManager->uploadFile($_FILES['file-background'], true, array('jpg','jpeg','png','bmp'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background');
               $fu2 = $fileManager->uploadFile($_FILES['file-ruleset'], false, array('pdf'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset');
               $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],$fu,$fu2,null, $_POST['edit-game-id']);
             } else if ($_FILES['file-ruleset']['name'] != "" && $_FILES['file-background']['name'] == "" && $_FILES['file-icon']['name'] != "") {

               $fu = $fileManager->uploadFile($_FILES['file-icon'], true, array('jpg','jpeg','png','bmp','svg'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-icon');
               $fu2 = $fileManager->uploadFile($_FILES['file-ruleset'], false, array('pdf'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset');
               $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],null,$fu,$fu2, $_POST['edit-game-id']);

             } else if ($_FILES['file-ruleset']['name'] == "" && $_FILES['file-background']['name'] != "" && $_FILES['file-icon']['name'] != "") {

               $fu = $fileManager->uploadFile($_FILES['file-background'], true, array('jpg','jpeg','png','bmp'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background');
               $fu2 = $fileManager->uploadFile($_FILES['file-icon'], true, array('jpg','jpeg','png','bmp','svg'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-icon');
               $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],$fu,null,$fu2, $_POST['edit-game-id']);

             } else if ($_FILES['file-ruleset']['name'] == "" && $_FILES['file-background']['name'] == "" && $_FILES['file-icon']['name'] == "") {

               $fu = $fileManager->uploadFile($_FILES['file-background'], true, array('jpg','jpeg','png','bmp'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background');
               $fu3 = $fileManager->uploadFile($_FILES['file-ruleset'], false, array('jpg','jpeg','png','bmp'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset');
               $fu2 = $fileManager->uploadFile($_FILES['file-icon'], true, array('jpg','jpeg','png','bmp','svg'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-icon');
               $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],$fu,$fu3,$fu2, $_POST['edit-game-id']);
             }

              $this->log("Game has been edited","game_edit");
              $this->addMessage("Game has been edited");
              $this->redir("administration");
            } catch (PDOException $e) {
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
              if ($_POST['verification_username'] > 0) {
                if (preg_match("/^[a-zA-Z0-9]+#[a-fA-F0-9]{4}$/", $_POST['user-to-invite']) || preg_match("/^#[a-fA-F0-9]{4}$/", $_POST['user-to-invite'])) {
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
            } else
                $this->addMessage("Field empty");
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
              } else
                $this->addMessage("Field empty");
            }

          } catch (PDOException $e) {
            $this->addMessage($e);
          }
        }
      }


    }
  }

 ?>
