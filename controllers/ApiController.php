<?php
  class ApiController extends Controller {
    public function parse($params) {
      $bracketManager = new BracketManager();
      $eventManager = new EventManager();
      $teamManager = new TeamManager();

      if (empty($params[0]) || empty($params[1])) {
        $this->redir("error");
      }

        if ($params[0] == "match") {
          $matchIds = $bracketManager->returnMatchIds();
            if (!empty($params[1]) && in_array($params[1],$matchIds)) {
              if (!UserManager::authAdmin() && !UserManager::authWatchman() && $_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
                header("HTTP/1.1 401 Unauthorized");
                exit;
              }
              $match = $bracketManager->returnMatchById($params[1]);
              $this->data['response'] = $match;
            }
        } else if ($params[0] == "bracket") {
            if (!empty($params[1])) {
              $event = $eventManager->returnEventById($params[1]);
              $this->data['event'] = $event;
              if ($event['bracket_status'] == 'live') {
                $this->data['hasBrackets'] = true;
                $this->data['response'] = $bracketManager->returnParsedMatchesInEvent($event['event_id']);
              }
            } else {
              return;
            }
        } else if ($params[0] == "team") {
          if (!empty($params[1])) {
            $team = $teamManager->returnTeamById($params[1]);
            $this->data['response'] = $team;
          } else { return; }
        }

      $this->view = "raw";
    }
  }
?>
