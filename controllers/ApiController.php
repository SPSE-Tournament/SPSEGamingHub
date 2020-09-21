<?php
  class ApiController extends Controller
  {
      public function parse($params)
      {
          $bracketManager = new BracketManager();
          $eventManager = new EventManager();
          $teamManager = new TeamManager();
          $messageManager = new MessageManager();
          $userManager = new UserManager();

          if (!empty($params)) {

          switch ($params[0]) {
        default:
        $this->data['response'] = ['error' => "Bad endpoint"];
        break;
        case "match":
        $matchIds = $bracketManager->returnMatchIds();
          if (!empty($params[1]) && in_array($params[1], $matchIds)) {

              if (!UserManager::authAdmin() && !UserManager::authWatchman() && $_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
                  HTTP::status(403);
                  $this->redir("status/403");
              }
              $match = $bracketManager->returnMatchById($params[1]);
              $this->data['response'] = $match;
          } else {
            HTTP::status(400);
            $this->redir("status/400");
          }
          break;
        case "bracket":
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
        break;
        case "team":
        if (!empty($params[1])) {
            $team = $teamManager->returnTeamById($params[1]);
            $this->data['response'] = $team;
        } else {
            return;
        }
        break;
        case "messages":
        if ($userManager->returnUser() == null) {
            HTTP::status(403);
            $this->redir("status/403");
        }

        $this->data['response'] = $messageManager->returnMessages($_SESSION['user']['user_id']);
        if (!empty($params[1]) && !empty($params[2])) {
            $m = $messageManager;
            switch ($params[1]) {
            case "id":
            $this->data['response'] = $m->returnMessageById($params[2]);
            break;
            case "type":
            $this->data['response'] = $m->returnMessageByType($params[2]);
            break;
          }
        }
        break;
        case "teams":
        if ($userManager->returnUser() == null) {
            HTTP::status(403);
            $this->redir("status/403");
        }

        $this->data['response'] = $teamManager->returnTeams();
        if (!empty($params[1]) && !empty($params[2])) {
            switch ($params[1]) {
        case "id":
        $this->data['response'] = $teamManager->returnTeamById($params[2]);
        $this->data['response']['players'] = $teamManager->returnUsersInATeam($params[2]);
        break;
        case "livesearch":
        $this->data['response'] = $teamManager->liveSearchTeams($params[2]);
        break;
      }
        }
        break;
      }
    } else {
      $this->redir("error");
      }
          $this->view = "raw";
      }
  }
