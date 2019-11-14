<?php
  class AdministrationController extends Controller {
    public function parse($params) {
      $gameManager = new GameManager();
      $eventManager = new EventManager();
      $logManager = new LogManager();
      $fileManager = new FileManager();
      $stringManager = new StringManager();

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
        $this->header['page_title'] = "Admin Dashboard";
        $this->view = "administration";
      }

      //Handling POST requests
      if ($_POST) {
        if (isset($_POST['event-add'])) {
          try {
          $gamePL = $eventManager->getGameLimit($_POST['eventGame']);
          $eventManager->createEvent($_POST['eventName'], $_POST['eventGame'], $_POST['eventDate'] . " " .  $_POST['eventTime'], $_POST['eventPL'], $gamePL[0], $_POST['eventUrl']);
          $this->log("Global event has been created", "event_register");
          $this->addMessage("Global event has been created");
          $this->redir('events');
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
              if ($_FILES['file-ruleset']['name'] == "" && $_FILES['file-background']['name'] == "") {
                $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'], null, null, $_POST['edit-game-id']);
              } else if ($_FILES['file-ruleset']['name'] == "" && $_FILES['file-background']['name'] != "") {
                $fu = $fileManager->uploadFile($_FILES['file-background'], true, array('jpg','jpeg','png','bmp'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background');
                $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'], $fu,null, $_POST['edit-game-id']);
                $this->addMessage($res);
              } else if ($_FILES['file-ruleset']['name'] != "" && $_FILES['file-background']['name'] == "") {
                $fu = $fileManager->uploadFile($_FILES['file-ruleset'], false, array('pdf'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset');
                $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],null,$fu, $_POST['edit-game-id']);
              } else if ($_FILES['file-ruleset']['name'] != "" && $_FILES['file-background']['name'] != "") {
               $fu = $fileManager->uploadFile($_FILES['file-background'], true, array('jpg','jpeg','png','bmp'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-background');
               $fu2 = $fileManager->uploadFile($_FILES['file-ruleset'], false, array('pdf'), $stringManager->stripSpaces($_POST['mod-gameadd-shortname']).'-ruleset');
               $gameManager->editGame($_POST['mod-gameadd-name'],$_POST['mod-gameadd-shortname'], $_POST['mod-gameadd-playerlimit'],$fu,$fu2, $_POST['edit-game-id']);
             }
              $this->log("Game has been edited","game_edit");
              $this->addMessage("Game has been edited");
              $this->redir("administration");
            } catch (PDOException $e) {
              $this->addMessage($e);
            }
        }
      }


    }
  }

 ?>
