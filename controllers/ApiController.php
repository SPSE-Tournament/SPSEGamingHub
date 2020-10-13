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
          $gameManager = new GameManager();

          if (!empty($params)) {
              switch ($params[0]) {
        default:
        $this->data['response'] = ['error' => "Bad endpoint"];
        break;
        case "match":

        $matchIds = $bracketManager->returnMatchIds();
          if (!empty($params[1]) && in_array($params[1], $matchIds)) {
              if (!$userManager->checkPL("watchman")) {
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
        case "messages":

        if (!$userManager->checkPL("user")) {
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

        if (!$userManager->checkPL("user")) {
            HTTP::status(403);
            $this->redir("status/403");
        }
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
        } else {
            $this->data['response'] = $teamManager->returnTeams();
        }

        break;
        case "games":

        if (!$userManager->checkPL("watchman")) {
            HTTP::status(403);
            $this->redir("status/403");
        }
          if (!empty($params[1])) {
            if (!empty($params[2])) {

              switch ($params[1]) {
              default:
               $this->data['response'] = ['error' => "Bad endpoint"];
              break;
              case "id":
              $this->data['response'] = $gameManager->returnGameById($params[2]);
              break;
            }

            } else {
              HTTP::status(400);
              $this->redir("status/400");
            }

          } else {
              $this->data['response'] = $gameManager->returnGames();
          }

        break;
        case "users":

        if (!empty($params[1]) && !empty($params[2])) {
            switch ($params[1]) {
              case "name":
                $this->data['response'] = $userManager->selectUser($params[2]);
              break;
              case "livesearch":
              $this->data['response'] = $userManager->liveSearchUsers($params[2]);
              break;
            }
        } else if ($userManager->checkPL("admin")) {
          $this->data['response'] = $userManager->returnUsers();
        } else {
          HTTP::status(403);
          $this->redir("status/403");
        }

        break;
      }
          } else {
              HTTP::status(404);
              $this->redir("status/404");
          }
          $this->view = "raw";
      }
  }
