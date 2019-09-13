<?php
  class AdministrationController extends Controller {
    public function parse($params) {
      $gameManager = new GameManager();
      if ($_POST) {
        if (isset($_POST['event-add'])) {

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
      $this->data['games'] = $gameManager->returnGames();
      $this->view = "administration";
    }


  }

 ?>
