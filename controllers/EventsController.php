<?php
  class EventsController extends Controller {
    public function parse($params) {
      $eventManager = new EventManager();
      $gameManager = new GameManager();
      $teamMan = new TeamManager();
      $events = $eventManager->returnEvents();
      $eventUrls = array();
      for ($i=0; $i < count($events); $i++) {
            $eventUrls[] = $events[$i]['event_url'];
      }
      $this->data['admin'] = $_SESSION['admin'];

      if (!empty($params[0])) {

        if (in_array($params[0], $eventUrls)) {
          $this->data['event'] = $eventManager->returnEventByUrl($params[0]);

          $this->view = "event";
        } else if ($params[0] == 'edit' && !empty($params[1]) && in_array($params[1], $eventUrls)) {

              if (!UserManager::authAdmin()) {
                $this->addMessage("Admin rights needed.");
                $this->redir("home");
              }

              if ($_POST && isset($_POST['event-edit'])) {
                  $eventManager->updateEvent($_POST['eventName'], $_POST['eventGame'], $_POST['eventDate'], $_POST['eventTime'], $_POST['eventPL'], $_POST['eventUrl'], $params[1]);
                  $this->addMessage("Event has been updated");
                  $this->log("Event has been updated", 'event_edit');
                  $this->redir("events");
              }

              $this->data['event'] = $eventManager->returnEventByUrl($params[1]);
              $this->data['games'] = $gameManager->returnGames();
              $this->view = "editevent";
        } else {
          $this->redir("events");
        }
      } else {
        $this->data['events'] = $events;
        $this->data['user'] = $_SESSION['user'];
        $this->data['userTeams'] = $teamMan->returnUserTeams($_SESSION['user']['user_id']);
        $this->view = "events";
      }
    }
  }

?>
