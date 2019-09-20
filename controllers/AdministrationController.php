<?php
  class AdministrationController extends Controller {
    public function parse($params) {
      $gameManager = new GameManager();
      $eventManager = new EventManager();
      $logManager = new LogManager();
      if ($_POST) {
        if (isset($_POST['event-add'])) {
          try {
          $gamePL = $eventManager->getGameLimit($_POST['eventGame']);
          $eventManager->createEvent($_POST['eventName'], $_POST['eventGame'], $_POST['eventDate'] . " " .  $_POST['eventTime'], $_POST['eventPL'], $gamePL[0]);
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
      $this->data['logs'] = $logManager->returnLogs();
      $this->data['games'] = $gameManager->returnGames();
      $this->view = "administration";
    }


  }

 ?>
