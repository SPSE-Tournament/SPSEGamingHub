<?php
  class AdministrationController extends Controller {
    public function parse($params) {
      $gameManager = new GameManager();
      $eventManager = new EventManager();
      $logManager = new LogManager();

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
          $eventManager->createEvent($_POST['eventName'], $_POST['eventGame'], $_POST['eventDate'] . " " .  $_POST['eventTime'], $_POST['eventPL'], $gamePL[0], $_POST['$eventUrl']);
          $this->log("Global event has been created", "event_register");
          $this->addMessage("Global event has been created");
          $this->redir('events');
        } catch (PDOException $e) {
          $this->addMessage($e);
        }
        } elseif (isset($_POST['game-add'])) {
            try {
              $gameManager->addGame($_POST['mod-gameadd-name'], $_POST['mod-gameadd-playerlimit']);
              $this->log("Game has been added","game_add");
              $this->addMessage("Game has been added");
            } catch (PDOException $e) {
              $this->addMessage($e);
            }
        }
      }


    }
  }

 ?>
